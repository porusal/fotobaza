<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Support\SiteViewData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PageController extends Controller
{
    public function index(): View
    {
        return view('admin.pages.index', array_merge(SiteViewData::common(), [
            'pages' => Page::query()->orderBy('title')->get(),
        ]));
    }

    public function create(): View
    {
        return view('admin.pages.create', array_merge(SiteViewData::common(), [
            'page' => new Page([
                'is_published' => true,
                'show_in_menu' => true,
            ]),
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePayload($request);

        $page = new Page();
        $page->fill($this->mapPayload($request, $validated));
        if ($request->hasFile('image')) {
            $page->image = $this->storeImage($request);
        }
        $page->slug = $this->uniqueSlug($validated['slug'] ?? null, $page->title);
        $page->save();

        return redirect()
            ->route('admin.pages.edit', $page)
            ->with('status', 'Страница создана.');
    }

    public function edit(Page $page): View
    {
        return view('admin.pages.edit', array_merge(SiteViewData::common(), [
            'page' => $page,
        ]));
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        $validated = $this->validatePayload($request, $page);
        $payload = $this->mapPayload($request, $validated);

        if ($request->hasFile('image')) {
            $this->deleteStoredAsset($page->image);
            $payload['image'] = $this->storeImage($request);
        } else {
            unset($payload['image']);
        }

        $page->fill($payload);
        $page->slug = $this->uniqueSlug($validated['slug'] ?? null, $page->title, $page);
        $page->save();

        return redirect()
            ->route('admin.pages.edit', $page)
            ->with('status', 'Страница сохранена.');
    }

    public function destroy(Page $page): RedirectResponse
    {
        $this->deleteStoredAsset($page->image);
        $page->delete();

        return redirect()
            ->route('admin.pages.index')
            ->with('status', 'Страница удалена.');
    }

    private function validatePayload(Request $request, ?Page $page = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:4096'],
            'is_published' => ['nullable', 'boolean'],
            'show_in_menu' => ['nullable', 'boolean'],
        ]);
    }

    private function mapPayload(Request $request, array $validated): array
    {
        return [
            'title' => $validated['title'],
            'content' => $validated['content'] ?? null,
            'is_published' => $request->boolean('is_published'),
            'show_in_menu' => $request->boolean('show_in_menu'),
        ];
    }

    private function storeImage(Request $request): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        $path = $request->file('image')->store('pages', 'public');

        return Storage::disk('public')->url($path);
    }

    private function deleteStoredAsset(?string $path): void
    {
        if (! $path || ! Str::startsWith($path, '/storage/')) {
            return;
        }

        Storage::disk('public')->delete(Str::after($path, '/storage/'));
    }

    private function uniqueSlug(?string $slug, string $fallback, ?Page $page = null): string
    {
        $base = Str::slug($slug ?: $fallback) ?: 'page';
        $candidate = $base;
        $index = 2;

        while (
            Page::query()
                ->when($page, fn ($query) => $query->whereKeyNot($page->getKey()))
                ->where('slug', $candidate)
                ->exists()
        ) {
            $candidate = $base . '-' . $index++;
        }

        return $candidate;
    }
}
