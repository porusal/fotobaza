<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use App\Models\Photo;
use App\Models\Tag;
use App\Support\SiteViewData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PhotoController extends Controller
{
    public function index(): View
    {
        return view('admin.photos.index', array_merge(SiteViewData::common(), [
            'photos' => Photo::query()
                ->with(['gallery:id,display_name', 'tags:id,name'])
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->limit(60)
                ->get(),
        ]));
    }

    public function create(): View
    {
        return view('admin.photos.create', array_merge(SiteViewData::common(), [
            'galleries' => Gallery::query()->active()->ordered()->get(['id', 'display_name']),
            'tags' => Tag::query()->ordered()->get(['id', 'name']),
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'gallery_id' => ['required', 'integer', Rule::exists('galleries', 'id')],
            'items' => ['required', 'array', 'min:1'],
            'items.*.file' => ['required', 'file', 'image', 'max:8192'],
            'items.*.alt_text' => ['nullable', 'string', 'max:255'],
            'items.*.sort_order' => ['nullable', 'integer', 'min:0', 'max:99999'],
            'items.*.tags' => ['nullable', 'array'],
            'items.*.tags.*' => ['integer', Rule::exists('tags', 'id')],
        ]);

        $gallery = Gallery::query()->findOrFail((int) $validated['gallery_id']);

        foreach ($validated['items'] as $index => $item) {
            $file = $request->file("items.$index.file");

            if (! $file) {
                continue;
            }

            $storedPath = $file->store('photos/' . $gallery->slug, 'public');
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

    public function update(Request $request, Photo $photo): RedirectResponse
    {
        $validated = $request->validate([
            'gallery_id' => ['required', 'integer', Rule::exists('galleries', 'id')],
            'filename' => ['required', 'string', 'max:255'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:99999'],
            'photo_file' => ['nullable', 'file', 'image', 'max:8192'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['integer', Rule::exists('tags', 'id')],
        ]);

        $gallery = Gallery::query()->findOrFail((int) $validated['gallery_id']);

        if ($request->hasFile('photo_file')) {
            $this->deleteStoredAsset($photo->path);

            $storedPath = $request->file('photo_file')->store('photos/' . $gallery->slug, 'public');
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

    public function destroy(Photo $photo): RedirectResponse
    {
        $this->deleteStoredAsset($photo->path);
        $photo->delete();

        return redirect()
            ->route('admin.photos.index')
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
