<?php

namespace App\Support;

use App\Models\Gallery;
use App\Models\Photo;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class GalleryFilesystem
{
    public function directoryForGallery(Gallery $gallery): string
    {
        $segments = [];
        $current = $gallery;
        $guard = 0;

        while ($current && $guard < 50) {
            array_unshift($segments, $this->folderSegment($current));
            $current = $this->parentFor($current);
            $guard++;
        }

        return $this->normalizeRelativePath($this->photosRootDirectory() . '/' . implode('/', $segments));
    }

    public function ensureDirectoryForGallery(Gallery $gallery): string
    {
        $directory = $this->directoryForGallery($gallery);
        $this->ensureDirectory($directory);

        return $directory;
    }

    public function moveDirectoryForGallery(Gallery $gallery, string $oldDirectory): string
    {
        $oldDirectory = $this->normalizeRelativePath($oldDirectory);
        $newDirectory = $this->directoryForGallery($gallery);

        if ($oldDirectory === $newDirectory) {
            $this->ensureDirectory($newDirectory);

            return $newDirectory;
        }

        $this->moveDirectory($oldDirectory, $newDirectory);
        $this->replaceStoredAssetPaths($oldDirectory, $newDirectory);

        return $newDirectory;
    }

    public function photosRootDirectory(): string
    {
        return $this->normalizeRelativePath((string) config('gallery.filesystem.photos_directory', 'photos')) ?: 'photos';
    }

    private function moveDirectory(string $oldDirectory, string $newDirectory): void
    {
        $disk = Storage::disk('public');
        $oldPath = $disk->path($oldDirectory);
        $newPath = $disk->path($newDirectory);

        if (! is_dir($oldPath)) {
            $this->ensureDirectory($newDirectory);

            return;
        }

        if (is_dir($newPath)) {
            if ($this->sameDirectory($oldPath, $newPath)) {
                return;
            }

            if (! $this->directoryIsEmpty($newPath)) {
                throw new RuntimeException('Целевая папка уже существует и не пуста: ' . $newDirectory);
            }

            @rmdir($newPath);
        }

        $parentPath = dirname($newPath);

        if (! is_dir($parentPath) && ! @mkdir($parentPath, 0775, true) && ! is_dir($parentPath)) {
            throw new RuntimeException('Не удалось создать родительскую папку: ' . dirname($newDirectory));
        }

        if (! @rename($oldPath, $newPath)) {
            throw new RuntimeException('Не удалось переместить папку: ' . $oldDirectory . ' -> ' . $newDirectory);
        }
    }

    private function ensureDirectory(string $directory): void
    {
        $path = Storage::disk('public')->path($directory);

        if (is_dir($path)) {
            return;
        }

        if (! @mkdir($path, 0775, true) && ! is_dir($path)) {
            throw new RuntimeException('Не удалось создать папку: ' . $directory);
        }
    }

    private function replaceStoredAssetPaths(string $oldDirectory, string $newDirectory): void
    {
        Photo::query()
            ->orderBy('id')
            ->chunkById(200, function ($photos) use ($oldDirectory, $newDirectory): void {
                foreach ($photos as $photo) {
                    $nextPath = $this->replaceStorageUrlPath((string) $photo->path, $oldDirectory, $newDirectory);

                    if ($nextPath !== $photo->path) {
                        $photo->forceFill(['path' => $nextPath])->save();
                    }
                }
            });

        Gallery::query()
            ->whereNotNull('cover_image')
            ->orderBy('id')
            ->chunkById(200, function ($galleries) use ($oldDirectory, $newDirectory): void {
                foreach ($galleries as $gallery) {
                    $nextPath = $this->replaceStorageUrlPath((string) $gallery->cover_image, $oldDirectory, $newDirectory);

                    if ($nextPath !== $gallery->cover_image) {
                        $gallery->forceFill(['cover_image' => $nextPath])->save();
                    }
                }
            });
    }

    private function replaceStorageUrlPath(string $value, string $oldDirectory, string $newDirectory): string
    {
        $oldPublicPath = '/storage/' . $this->normalizeRelativePath($oldDirectory);
        $newPublicPath = '/storage/' . $this->normalizeRelativePath($newDirectory);
        $path = parse_url($value, PHP_URL_PATH) ?: $value;

        if ($path !== $oldPublicPath && ! str_starts_with($path, $oldPublicPath . '/')) {
            return $value;
        }

        $newPath = $newPublicPath . substr($path, strlen($oldPublicPath));

        return str_replace($path, $newPath, $value);
    }

    private function parentFor(Gallery $gallery): ?Gallery
    {
        if (! $gallery->parent_id) {
            return null;
        }

        if ($gallery->relationLoaded('parent')) {
            return $gallery->parent;
        }

        return Gallery::query()
            ->select(['id', 'parent_id', 'name'])
            ->find($gallery->parent_id);
    }

    private function folderSegment(Gallery $gallery): string
    {
        $segment = trim(str_replace(["\0", '/', '\\'], '-', (string) $gallery->name));

        return $segment !== '' ? $segment : 'gallery-' . ($gallery->id ?: 'new');
    }

    private function normalizeRelativePath(string $path): string
    {
        return trim(str_replace('\\', '/', $path), '/');
    }

    private function sameDirectory(string $left, string $right): bool
    {
        $left = realpath($left) ?: $left;
        $right = realpath($right) ?: $right;

        return rtrim(str_replace('\\', '/', $left), '/') === rtrim(str_replace('\\', '/', $right), '/');
    }

    private function directoryIsEmpty(string $path): bool
    {
        $items = @scandir($path);

        return $items === false || count(array_diff($items, ['.', '..'])) === 0;
    }
}
