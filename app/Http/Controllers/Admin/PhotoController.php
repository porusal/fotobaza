<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use App\Models\Photo;
use App\Models\Tag;
use App\Support\GalleryFilesystem;
use App\Support\SiteViewData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PhotoController extends Controller
{
    public function index(Request $request): View
    {
        $selectedGalleryId = $request->integer('gallery');
        $selectedGallery = null;

        if ($selectedGalleryId > 0) {
            $selectedGallery = Gallery::query()
                ->with([
                    'parent:id,display_name',
                    'photos' => fn ($query) => $query->with('tags:id,name'),
                ])
                ->withCount('photos')
                ->findOrFail($selectedGalleryId);
        }

        return view('admin.photos.index', array_merge(SiteViewData::common(), [
            'galleries' => Gallery::query()
                ->with('parent:id,display_name')
                ->withCount('photos')
                ->ordered()
                ->get(),
            'selectedGallery' => $selectedGallery,
            'photosCount' => Photo::query()->count(),
        ]));
    }

    public function create(): View
    {
        return view('admin.photos.create', array_merge(SiteViewData::common(), [
            'galleries' => Gallery::query()->active()->ordered()->get(['id', 'display_name']),
            'tags' => Tag::query()->ordered()->get(['id', 'name']),
        ]));
    }

    public function store(Request $request, GalleryFilesystem $galleryFilesystem): RedirectResponse
    {
        $validated = $request->validate([
            'gallery_id' => ['required', 'integer', Rule::exists('galleries', 'id')],
            'items' => ['required', 'array', 'min:1'],
            'items.*.file' => ['required', 'file', 'image', 'max:65536'],
            'items.*.alt_text' => ['nullable', 'string', 'max:255'],
            'items.*.sort_order' => ['nullable', 'integer', 'min:0', 'max:99999'],
            'items.*.tags' => ['nullable', 'array'],
            'items.*.tags.*' => ['integer', Rule::exists('tags', 'id')],
        ], [
            'items.*.file.uploaded' => 'Не удалось загрузить фото. Попробуйте выбрать файл еще раз.',
            'items.*.file.image' => 'Выберите изображение в поддерживаемом формате.',
        ]);

        $gallery = Gallery::query()->findOrFail((int) $validated['gallery_id']);
        $galleryDirectory = $galleryFilesystem->ensureDirectoryForGallery($gallery);

        foreach ($validated['items'] as $index => $item) {
            $file = $request->file("items.$index.file");

            if (! $file) {
                continue;
            }

            $storedPath = $file->store($galleryDirectory, 'public');
            $photo = Photo::create([
                'gallery_id' => $gallery->id,
                'filename' => $file->getClientOriginalName(),
                'path' => Storage::disk('public')->url($storedPath),
                'alt_text' => $item['alt_text'] ?? $file->getClientOriginalName(),
                'sort_order' => (int) ($item['sort_order'] ?? (($index + 1) * 10)),
            ]);

            $photo->tags()->sync(array_map('intval', $item['tags'] ?? []));
        }

        return redirect()
            ->route('admin.photos.index')
            ->with('status', 'Фото загружены.');
    }

    public function edit(Photo $photo): View
    {
        $photo->load(['gallery:id,display_name', 'tags:id,name']);

        return view('admin.photos.edit', array_merge(SiteViewData::common(), [
            'photo' => $photo,
            'galleries' => Gallery::query()->active()->ordered()->get(['id', 'display_name']),
            'tags' => Tag::query()->ordered()->get(['id', 'name']),
        ]));
    }

    public function update(Request $request, Photo $photo, GalleryFilesystem $galleryFilesystem): RedirectResponse
    {
        $validated = $request->validate([
            'gallery_id' => ['required', 'integer', Rule::exists('galleries', 'id')],
            'filename' => ['required', 'string', 'max:255'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:99999'],
            'photo_file' => ['nullable', 'file', 'image', 'max:65536'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['integer', Rule::exists('tags', 'id')],
        ], [
            'photo_file.uploaded' => 'Не удалось загрузить фото. Попробуйте выбрать файл еще раз.',
            'photo_file.image' => 'Выберите изображение в поддерживаемом формате.',
        ]);

        $gallery = Gallery::query()->findOrFail((int) $validated['gallery_id']);

        if ($request->hasFile('photo_file')) {
            $this->deleteStoredAsset($photo->path);

            $storedPath = $request->file('photo_file')->store($galleryFilesystem->ensureDirectoryForGallery($gallery), 'public');
            $photo->path = Storage::disk('public')->url($storedPath);
        }

        $photo->gallery_id = $gallery->id;
        $photo->filename = $validated['filename'];
        $photo->alt_text = $validated['alt_text'] ?? null;
        $photo->sort_order = (int) ($validated['sort_order'] ?? $photo->sort_order ?? 0);
        $photo->save();
        $photo->tags()->sync(array_map('intval', $validated['tags'] ?? []));

        return redirect()
            ->route('admin.photos.edit', $photo)
            ->with('status', 'Фото сохранено.');
    }

    public function destroy(Request $request, Photo $photo): RedirectResponse
    {
        $redirectGalleryId = $request->integer('redirect_gallery');

        $this->deleteStoredAsset($photo->path);
        $photo->delete();

        return redirect()
            ->route('admin.photos.index', $redirectGalleryId > 0 ? ['gallery' => $redirectGalleryId] : [])
            ->with('status', 'Фото удалено.');
    }

    private function deleteStoredAsset(?string $path): void
    {
        if (! $path || ! Str::startsWith($path, '/storage/')) {
            return;
        }

        Storage::disk('public')->delete(Str::after($path, '/storage/'));
    }
}
