<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use App\Support\SiteViewData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TagController extends Controller
{
    public function index(): View
    {
        return view('admin.tags.index', array_merge(SiteViewData::common(), [
            'tags' => Tag::query()
                ->withCount('photos')
                ->ordered()
                ->get(['id', 'name']),
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80', 'unique:tags,name'],
        ]);

        Tag::create([
            'name' => trim($validated['name']),
        ]);

        return redirect()
            ->route('admin.tags.index')
            ->with('status', 'Тег добавлен.');
    }

    public function update(Request $request, Tag $tag): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80', Rule::unique('tags', 'name')->ignore($tag->id)],
        ]);

        $tag->update([
            'name' => trim($validated['name']),
        ]);

        return redirect()
            ->route('admin.tags.index')
            ->with('status', 'Тег сохранён.');
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        $tag->delete();

        return redirect()
            ->route('admin.tags.index')
            ->with('status', 'Тег удалён.');
    }
}
