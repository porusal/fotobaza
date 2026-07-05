<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use App\Models\Page;
use App\Models\Photo;
use App\Models\Setting;
use App\Support\SiteViewData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', array_merge(SiteViewData::common(), [
            'photosCount' => Photo::query()->count(),
            'galleriesCount' => Gallery::query()->count(),
            'pagesCount' => Page::query()->count(),
        ]));
    }

    public function galleries(): View
    {
        return view('admin.galleries.index', array_merge(SiteViewData::common(), [
            'galleries' => Gallery::query()
                ->with('parent:id,display_name')
                ->ordered()
                ->get(),
        ]));
    }

    public function photos(): View
    {
        return view('admin.photos.index', array_merge(SiteViewData::common(), [
            'galleries' => Gallery::query()->active()->ordered()->get(['id', 'display_name']),
            'tags' => \App\Models\Tag::query()->ordered()->get(),
            'photos' => Photo::query()
                ->with(['gallery:id,display_name', 'tags:id,name'])
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->limit(48)
                ->get(),
        ]));
    }

    public function pages(): View
    {
        return view('admin.pages.index', array_merge(SiteViewData::common(), [
            'pages' => Page::query()->orderBy('title')->get(),
        ]));
    }

    public function settings(): View
    {
        return view('admin.settings', array_merge(SiteViewData::common(), [
            'settings' => SiteViewData::settings(),
        ]));
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'site_logo' => ['nullable', 'image', 'max:2048'],
            'hero_image' => ['nullable', 'image', 'max:4096'],
            'site_name' => ['required', 'string', 'max:255'],
            'site_tagline' => ['required', 'string', 'max:255'],
            'intro_text' => ['nullable', 'string'],
            'home_photos_count' => ['required', 'integer', 'min:1', 'max:50'],
            'gallery_grid_columns' => ['required', 'integer', 'min:1', 'max:4'],
            'grid_gap' => ['required', 'in:sm,md,lg'],
        ]);

        if ($request->hasFile('site_logo')) {
            $currentLogo = Setting::value('site_logo');
            if ($currentLogo && Str::startsWith($currentLogo, '/storage/')) {
                Storage::disk('public')->delete(Str::after($currentLogo, '/storage/'));
            }

            $storedPath = $request->file('site_logo')->store('site', 'public');
            $validated['site_logo'] = Storage::disk('public')->url($storedPath);
        } else {
            unset($validated['site_logo']);
        }

        if ($request->hasFile('hero_image')) {
            $currentHero = Setting::value('hero_image');
            if ($currentHero && Str::startsWith($currentHero, '/storage/')) {
                Storage::disk('public')->delete(Str::after($currentHero, '/storage/'));
            }

            $storedPath = $request->file('hero_image')->store('site', 'public');
            $validated['hero_image'] = Storage::disk('public')->url($storedPath);
        } else {
            unset($validated['hero_image']);
        }

        foreach ($validated as $key => $value) {
            Setting::put($key, $value);
        }

        return redirect()
            ->route('admin.settings.edit')
            ->with('status', 'Настройки сохранены.');
    }
}
