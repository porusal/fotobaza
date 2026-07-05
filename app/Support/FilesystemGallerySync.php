<?php

namespace App\Support;

use App\Models\Gallery;
use App\Models\Photo;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Throwable;

class FilesystemGallerySync
{
    private const AUTO_SYNC_MARKER_FILENAME = 'gallery-filesystem-auto-sync.timestamp';

    public function syncIfDue(): array
    {
        if (! config('gallery.filesystem.enabled', true) || ! config('gallery.filesystem.auto_sync', true)) {
            return $this->emptyResult(true);
        }

        $seconds = max(0, (int) config('gallery.filesystem.auto_sync_seconds', 30));

        if ($seconds > 0 && ! $this->autoSyncIsDue($seconds)) {
            return $this->emptyResult(true);
        }

        if ($seconds > 0) {
            $this->touchAutoSyncMarker();
        }

        try {
            return $this->sync();
        } catch (Throwable $exception) {
            Log::warning('Filesystem gallery sync failed.', [
                'message' => $exception->getMessage(),
            ]);

            return $this->emptyResult(true, $exception->getMessage());
        }
    }

    public function sync(): array
    {
        $result = $this->emptyResult();

        if (! config('gallery.filesystem.enabled', true)) {
            $result['skipped'] = true;

            return $result;
        }

        $disk = Storage::disk('public');
        $rootDirectory = $this->normalizeRelativePath((string) config('gallery.filesystem.photos_directory', 'photos'));
        $absoluteRoot = $disk->path($rootDirectory);

        if (! is_dir($absoluteRoot)) {
            if (! @mkdir($absoluteRoot, 0775, true) && ! is_dir($absoluteRoot)) {
                $result['error'] = 'Cannot create photos directory: ' . $absoluteRoot;

                return $result;
            }
        }

        $galleriesByPath = [];
        $syncedGalleries = [];

        foreach ($this->directories($absoluteRoot) as $relativeDirectory => $absoluteDirectory) {
            $parentPath = $this->parentPath($relativeDirectory);
            $parent = $parentPath !== null ? ($galleriesByPath[$parentPath] ?? null) : null;
            $gallery = $this->syncGallery($relativeDirectory, $parent, $result);

            $galleriesByPath[$relativeDirectory] = $gallery;
            $syncedGalleries[$relativeDirectory] = $gallery;

            $this->syncPhotos($gallery, $absoluteDirectory, $rootDirectory . '/' . $relativeDirectory, $result);
        }

        $this->refreshCovers($syncedGalleries);

        return $result;
    }

    private function syncGallery(string $relativeDirectory, ?Gallery $parent, array &$result): Gallery
    {
        $name = $this->basename($relativeDirectory);
        $displayName = $this->displayName($name);
        $slug = $this->slugForPath($relativeDirectory);

        $gallery = Gallery::query()->where('slug', $slug)->first();

        if (! $gallery) {
            $gallery = Gallery::query()
                ->where('parent_id', $parent?->id)
                ->where('name', $name)
                ->first();
        }

        if (! $gallery) {
            $gallery = Gallery::create([
                'parent_id' => $parent?->id,
                'name' => Str::limit($name, 255, ''),
                'display_name' => Str::limit($displayName, 255, ''),
                'slug' => $this->uniqueSlug($slug),
                'description' => null,
                'cover_image' => null,
                'is_active' => true,
                'sort_order' => $this->nextSortOrder($parent),
            ]);

            $result['galleries_created']++;

            return $gallery;
        }

        $updates = [];

        if ((int) ($gallery->parent_id ?? 0) !== (int) ($parent?->id ?? 0)) {
            $updates['parent_id'] = $parent?->id;
        }

        if ($gallery->name !== $name) {
            $updates['name'] = Str::limit($name, 255, '');
        }

        if (! $gallery->display_name) {
            $updates['display_name'] = Str::limit($displayName, 255, '');
        }

        if (! $gallery->is_active) {
            $updates['is_active'] = true;
        }

        if ($updates !== []) {
            $gallery->forceFill($updates)->save();
            $result['galleries_updated']++;
        }

        return $gallery->refresh();
    }

    private function syncPhotos(Gallery $gallery, string $absoluteDirectory, string $diskDirectory, array &$result): void
    {
        foreach ($this->imageFiles($absoluteDirectory) as $index => $file) {
            $filename = $file->getFilename();
            $diskPath = $this->normalizeRelativePath($diskDirectory . '/' . $filename);
            $url = Storage::disk('public')->url($diskPath);

            $result['photos_scanned']++;

            $photo = Photo::query()->where('path', $url)->first();

            if (! $photo) {
                Photo::create([
                    'gallery_id' => $gallery->id,
                    'filename' => Str::limit($filename, 255, ''),
                    'path' => $url,
                    'alt_text' => $this->altText($filename),
                    'sort_order' => ($index + 1) * 10,
                ]);

                $result['photos_created']++;

                continue;
            }

            $updates = [];

            if ((int) $photo->gallery_id !== (int) $gallery->id) {
                $updates['gallery_id'] = $gallery->id;
            }

            if ($photo->filename !== $filename) {
                $updates['filename'] = Str::limit($filename, 255, '');
            }

            if (! $photo->alt_text) {
                $updates['alt_text'] = $this->altText($filename);
            }

            if ($updates !== []) {
                $photo->forceFill($updates)->save();
                $result['photos_updated']++;
            }
        }
    }

    /**
     * @param array<string, Gallery> $galleries
     */
    private function refreshCovers(array $galleries): void
    {
        uksort($galleries, fn (string $left, string $right) => substr_count($right, '/') <=> substr_count($left, '/'));

        foreach ($galleries as $gallery) {
            $gallery->refresh();

            if ($gallery->cover_image) {
                continue;
            }

            $cover = $gallery->photos()->ordered()->value('path')
                ?: $gallery->children()->whereNotNull('cover_image')->value('cover_image');

            if ($cover) {
                $gallery->forceFill(['cover_image' => $cover])->save();
            }
        }
    }

    /**
     * @return array<string, string>
     */
    private function directories(string $absoluteRoot): array
    {
        $directories = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($absoluteRoot, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            if (! $item instanceof SplFileInfo || ! $item->isDir() || $item->isLink()) {
                continue;
            }

            $relativePath = $this->relativePath($absoluteRoot, $item->getPathname());

            if ($relativePath === '' || $this->isHiddenPath($relativePath)) {
                continue;
            }

            $directories[$relativePath] = $item->getPathname();
        }

        uksort($directories, function (string $left, string $right): int {
            $depth = substr_count($left, '/') <=> substr_count($right, '/');

            return $depth !== 0 ? $depth : strnatcasecmp($left, $right);
        });

        return $directories;
    }

    /**
     * @return array<int, SplFileInfo>
     */
    private function imageFiles(string $absoluteDirectory): array
    {
        $extensions = array_flip(array_map('strtolower', (array) config('gallery.filesystem.image_extensions', [])));
        $files = [];

        foreach (new RecursiveDirectoryIterator($absoluteDirectory, RecursiveDirectoryIterator::SKIP_DOTS) as $file) {
            if (! $file instanceof SplFileInfo || ! $file->isFile() || $file->isLink()) {
                continue;
            }

            $filename = $file->getFilename();

            if (str_starts_with($filename, '.') || str_starts_with($filename, '_')) {
                continue;
            }

            if (! isset($extensions[strtolower($file->getExtension())])) {
                continue;
            }

            $files[] = $file;
        }

        usort($files, fn (SplFileInfo $left, SplFileInfo $right) => strnatcasecmp($left->getFilename(), $right->getFilename()));

        return $files;
    }

    private function slugForPath(string $relativeDirectory): string
    {
        $parts = array_filter(explode('/', $this->normalizeRelativePath($relativeDirectory)), fn (string $part) => $part !== '');
        $slugs = array_map(function (string $part): string {
            $slug = Str::slug($part);

            return $slug !== '' ? $slug : 'folder-' . substr(md5($part), 0, 8);
        }, $parts);

        return implode('-', $slugs) ?: 'photos';
    }

    private function uniqueSlug(string $slug): string
    {
        $base = $slug !== '' ? $slug : 'gallery';
        $candidate = $base;
        $index = 2;

        while (Gallery::query()->where('slug', $candidate)->exists()) {
            $candidate = $base . '-' . $index++;
        }

        return $candidate;
    }

    private function nextSortOrder(?Gallery $parent): int
    {
        $max = Gallery::query()
            ->where('parent_id', $parent?->id)
            ->max('sort_order');

        return ((int) $max) + 10;
    }

    private function relativePath(string $root, string $path): string
    {
        $root = rtrim(str_replace('\\', '/', $root), '/');
        $path = str_replace('\\', '/', $path);

        return $this->normalizeRelativePath(Str::after($path, $root . '/'));
    }

    private function parentPath(string $relativeDirectory): ?string
    {
        $relativeDirectory = $this->normalizeRelativePath($relativeDirectory);
        $parent = dirname($relativeDirectory);

        return $parent === '.' ? null : $this->normalizeRelativePath($parent);
    }

    private function basename(string $path): string
    {
        $parts = explode('/', $this->normalizeRelativePath($path));

        return (string) end($parts);
    }

    private function normalizeRelativePath(string $path): string
    {
        return trim(str_replace('\\', '/', $path), '/');
    }

    private function displayName(string $name): string
    {
        return trim((string) preg_replace('/[_-]+/u', ' ', $name)) ?: $name;
    }

    private function altText(string $filename): string
    {
        return $this->displayName(pathinfo($filename, PATHINFO_FILENAME));
    }

    private function isHiddenPath(string $relativePath): bool
    {
        foreach (explode('/', $relativePath) as $part) {
            if (str_starts_with($part, '.') || str_starts_with($part, '_')) {
                return true;
            }
        }

        return false;
    }

    private function autoSyncIsDue(int $seconds): bool
    {
        $markerPath = $this->autoSyncMarkerPath();

        if (! is_file($markerPath)) {
            return true;
        }

        $lastRunAt = (int) @filemtime($markerPath);

        return $lastRunAt <= 0 || (time() - $lastRunAt) >= $seconds;
    }

    private function touchAutoSyncMarker(): void
    {
        $markerPath = $this->autoSyncMarkerPath();
        $directory = dirname($markerPath);

        if (! is_dir($directory)) {
            @mkdir($directory, 0775, true);
        }

        @touch($markerPath);
    }

    private function autoSyncMarkerPath(): string
    {
        return storage_path('framework/cache/' . self::AUTO_SYNC_MARKER_FILENAME);
    }

    private function emptyResult(bool $skipped = false, ?string $error = null): array
    {
        return [
            'skipped' => $skipped,
            'error' => $error,
            'galleries_created' => 0,
            'galleries_updated' => 0,
            'photos_scanned' => 0,
            'photos_created' => 0,
            'photos_updated' => 0,
        ];
    }
}
