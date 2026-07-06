<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use App\Models\Page;
use App\Models\Photo;
use App\Models\Setting;
use App\Models\Tag;
use App\Support\SiteViewData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
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
            'tags' => Tag::query()->ordered()->get(),
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
            'translate_languages' => ['nullable', 'array'],
            'translate_languages.*' => ['string', Rule::in(SiteViewData::translationTargetLanguageCodes())],
            'theme_text_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_heading_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_muted_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_accent_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_accent_secondary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_accent_soft_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_tag_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_dark_text_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_dark_heading_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_dark_muted_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_dark_accent_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_dark_accent_secondary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_dark_accent_soft_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_dark_tag_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'font_body' => ['required', Rule::in(array_keys(SiteViewData::themeFontOptions()))],
            'font_heading' => ['required', Rule::in(array_keys(SiteViewData::themeFontOptions()))],
            'font_menu' => ['required', Rule::in(array_keys(SiteViewData::themeFontOptions()))],
            'font_catalog' => ['required', Rule::in(array_keys(SiteViewData::themeFontOptions()))],
            'font_tag' => ['required', Rule::in(array_keys(SiteViewData::themeFontOptions()))],
            'font_body_style' => ['required', Rule::in(array_keys(SiteViewData::themeFontStyleOptions()))],
            'font_heading_style' => ['required', Rule::in(array_keys(SiteViewData::themeFontStyleOptions()))],
            'font_menu_style' => ['required', Rule::in(array_keys(SiteViewData::themeFontStyleOptions()))],
            'font_catalog_style' => ['required', Rule::in(array_keys(SiteViewData::themeFontStyleOptions()))],
            'font_tag_style' => ['required', Rule::in(array_keys(SiteViewData::themeFontStyleOptions()))],
            'font_body_size' => ['required', Rule::in(array_keys(SiteViewData::themeFontSizeOptions()))],
            'font_heading_size' => ['required', Rule::in(array_keys(SiteViewData::themeFontSizeOptions()))],
            'font_menu_size' => ['required', Rule::in(array_keys(SiteViewData::themeFontSizeOptions()))],
            'font_catalog_size' => ['required', Rule::in(array_keys(SiteViewData::themeFontSizeOptions()))],
            'font_tag_size' => ['required', Rule::in(array_keys(SiteViewData::themeFontSizeOptions()))],
        ]);

        $validated['translate_languages'] = array_values(array_unique($validated['translate_languages'] ?? []));

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
