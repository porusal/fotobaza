<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use App\Support\FilesystemGallerySync;
use App\Support\GalleryFilesystem;
use App\Support\SiteViewData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;

class GalleryController extends Controller
{
    public function index(): View
    {
        return view('admin.galleries.index', array_merge(SiteViewData::common(), [
            'galleries' => Gallery::query()
                ->with('parent:id,display_name')
                ->ordered()
                ->get(),
        ]));
    }

    public function syncFilesystem(FilesystemGallerySync $filesystemGallerySync): RedirectResponse
    {
        $result = $filesystemGallerySync->sync();

        if (! empty($result['error'])) {
            return back()->with('status', 'FTP-синхронизация не выполнена: ' . $result['error']);
        }

        return back()->with('status', sprintf(
            'FTP-синхронизация выполнена: создано каталогов %d, обновлено каталогов %d, найдено файлов %d, добавлено фото %d, обновлено фото %d.',
            $result['galleries_created'] ?? 0,
            $result['galleries_updated'] ?? 0,
            $result['photos_scanned'] ?? 0,
            $result['photos_created'] ?? 0,
            $result['photos_updated'] ?? 0,
        ));
    }

    public function create(): View
    {
        return view('admin.galleries.create', array_merge(SiteViewData::common(), [
            'gallery' => new Gallery([
                'is_active' => true,
                'sort_order' => 0,
            ]),
            'parentOptions' => SiteViewData::galleryOptions(),
        ]));
    }

    public function store(Request $request, GalleryFilesystem $galleryFilesystem): RedirectResponse
    {
        $validated = $this->validatePayload($request);
        $gallery = null;

        try {
            DB::transaction(function () use (&$gallery, $galleryFilesystem, $request, $validated): void {
                $gallery = new Gallery();
                $gallery->fill($this->mapPayload($request, $validated));
                if ($request->hasFile('cover_image')) {
                    $gallery->cover_image = $this->storeCoverImage($request);
                }
                $gallery->slug = $this->uniqueSlug($validated['slug'] ?? null, $gallery->display_name);
                $gallery->save();

                $galleryFilesystem->ensureDirectoryForGallery($gallery);
            });
        } catch (RuntimeException $exception) {
            throw ValidationException::withMessages([
                'name' => $exception->getMessage(),
            ]);
        }

        return redirect()
            ->route('admin.galleries.edit', $gallery)
            ->with('status', 'Каталог создан.');
    }

    public function edit(Gallery $gallery): View
    {
        return view('admin.galleries.edit', array_merge(SiteViewData::common(), [
            'gallery' => $gallery,
            'parentOptions' => SiteViewData::galleryOptions($gallery),
        ]));
    }

    public function update(Request $request, Gallery $gallery, GalleryFilesystem $galleryFilesystem): RedirectResponse
    {
        $validated = $this->validatePayload($request, $gallery);
        $payload = $this->mapPayload($request, $validated);
        $oldDirectory = $galleryFilesystem->directoryForGallery($gallery);

        if ($request->hasFile('cover_image')) {
            $this->deleteStoredAsset($gallery->cover_image);
            $payload['cover_image'] = $this->storeCoverImage($request);
        } else {
            unset($payload['cover_image']);
        }

        try {
            DB::transaction(function () use ($gallery, $galleryFilesystem, $oldDirectory, $payload, $validated): void {
                $gallery->fill($payload);
                $gallery->slug = $this->uniqueSlug($validated['slug'] ?? null, $gallery->display_name, $gallery);
                $gallery->save();

                $galleryFilesystem->moveDirectoryForGallery($gallery->refresh(), $oldDirectory);
            });
        } catch (RuntimeException $exception) {
            throw ValidationException::withMessages([
                'name' => $exception->getMessage(),
            ]);
        }

        return redirect()
            ->route('admin.galleries.edit', $gallery)
            ->with('status', 'Каталог сохранён.');
    }

    public function destroy(Gallery $gallery): RedirectResponse
    {
        $this->deleteStoredAsset($gallery->cover_image);

        $gallery->photos()->pluck('path')->each(fn (string $path) => $this->deleteStoredAsset($path));
        $gallery->delete();

        return redirect()
            ->route('admin.galleries.index')
            ->with('status', 'Каталог удалён.');
    }

    private function validatePayload(Request $request, ?Gallery $gallery = null): array
    {
        $validated = $request->validate([
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('galleries', 'id'),
            ],
            'name' => ['required', 'string', 'max:255', 'not_regex:/[\/\\\\]/'],
            'display_name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'image', 'max:4096'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:99999'],
        ]);

        if ($gallery && isset($validated['parent_id']) && (int) $validated['parent_id'] !== 0) {
            $branchIds = $this->branchIds($gallery);

            if (in_array((int) $validated['parent_id'], $branchIds, true)) {
                throw ValidationException::withMessages([
                    'parent_id' => 'Нельзя выбрать текущий каталог или один из его подкаталогов.',
                ]);
            }
        }

        return $validated;
    }

    private function mapPayload(Request $request, array $validated): array
    {
        return [
            'parent_id' => $validated['parent_id'] ?? null,
            'name' => $validated['name'],
            'display_name' => $validated['display_name'],
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
        ];
    }

    private function storeCoverImage(Request $request): ?string
    {
        if (! $request->hasFile('cover_image')) {
            return null;
        }

        $path = $request->file('cover_image')->store('galleries', 'public');

        return Storage::disk('public')->url($path);
    }

    private function deleteStoredAsset(?string $path): void
    {
        if (! $path || ! Str::startsWith($path, '/storage/')) {
            return;
        }

        Storage::disk('public')->delete(Str::after($path, '/storage/'));
    }

    private function uniqueSlug(?string $slug, string $fallback, ?Gallery $gallery = null): string
    {
        $base = Str::slug($slug ?: $fallback) ?: 'gallery';
        $candidate = $base;
        $index = 2;

        while (
            Gallery::query()
                ->when($gallery, fn ($query) => $query->whereKeyNot($gallery->getKey()))
                ->where('slug', $candidate)
                ->exists()
        ) {
            $candidate = $base . '-' . $index++;
        }

        return $candidate;
    }

    private function branchIds(Gallery $gallery): array
    {
        $gallery->loadMissing('childrenRecursive');

        $ids = [$gallery->id];

        foreach ($gallery->childrenRecursive as $child) {
            $ids = array_merge($ids, $this->branchIds($child));
        }

        return array_values(array_unique($ids));
    }
}
