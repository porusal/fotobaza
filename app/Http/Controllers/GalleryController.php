<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use App\Models\Page;
use App\Models\Photo;
use App\Support\SiteViewData;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GalleryController extends Controller
{
    public function index(Request $request): View
    {
        $settings = SiteViewData::settings();
        $activeTag = $request->string('tag')->trim()->toString();
        $latestPhotos = SiteViewData::latestPhotosByTag($settings['home_photos_count'], $activeTag !== '' ? $activeTag : null);
        $aboutPage = Page::query()
            ->published()
            ->where('slug', 'about')
            ->first();

        return view('home', array_merge(SiteViewData::common(), [
            'introText' => $settings['intro_text']
                ?: ($aboutPage
                ? SiteViewData::previewText($aboutPage->content, 220)
                : $settings['site_tagline']),
            'heroBadge' => $settings['hero_badge'],
            'heroImage' => $settings['hero_image'],
            'latestPhotos' => $latestPhotos,
            'latestPhotosCount' => $latestPhotos->count(),
            'galleryCount' => Gallery::query()->active()->count(),
            'tagCount' => \App\Models\Tag::query()->count(),
            'topTags' => SiteViewData::topTags(),
            'activeTag' => $activeTag !== '' ? $activeTag : null,
        ]));
    }

    public function show(Request $request, Gallery $gallery): View
    {
        $gallery->load([
            'parent:id,parent_id,slug,display_name',
            'children' => fn ($query) => $query->active()->ordered()->withCount('photos'),
        ]);

        $activeTag = $request->string('tag')->trim()->toString();

        $photoQuery = Photo::query()
            ->where('gallery_id', $gallery->id)
            ->with('tags')
            ->orderBy('sort_order')
            ->orderByDesc('created_at');

        if ($activeTag !== '') {
            $photoQuery->whereHas('tags', fn ($query) => $query->where('name', $activeTag));
        }

        $photos = $photoQuery->get();
        $gallery->setRelation('photos', $photos);

        return view('galleries.show', array_merge(SiteViewData::common(), [
            'gallery' => $gallery,
            'galleryTags' => SiteViewData::galleryTags($gallery),
            'activeTag' => $activeTag !== '' ? $activeTag : null,
            'galleryTreeOpenIds' => SiteViewData::galleryOpenIds($gallery),
        ]));
    }

    public function about(): View
    {
        return view('pages.about', array_merge(SiteViewData::common(), [
            'page' => SiteViewData::aboutPage(),
        ]));
    }

    public function page(Page $page): View
    {
        abort_unless($page->is_published, 404);

        return view('pages.show', array_merge(SiteViewData::common(), [
            'page' => $page,
        ]));
    }
}
