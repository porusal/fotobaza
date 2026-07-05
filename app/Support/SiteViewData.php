<?php

namespace App\Support;

use App\Models\Gallery;
use App\Models\Page;
use App\Models\Photo;
use App\Models\Setting;
use App\Models\Tag;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class SiteViewData
{
    public static function settings(): array
    {
        $defaults = [
            'site_name' => config('app.name', 'Foto 636'),
            'site_tagline' => 'Современная фотогалерея с атмосферой',
            'site_logo' => null,
            'hero_image' => null,
            'home_photos_count' => 12,
            'gallery_grid_columns' => 3,
            'grid_gap' => 'md',
            'hero_badge' => 'Now booking / editorial work',
            'intro_text' => null,
        ];

        $stored = Setting::query()->pluck('value', 'key')->all();
        $settings = array_merge($defaults, $stored);

        $settings['site_name'] = (string) ($settings['site_name'] ?: $defaults['site_name']);
        $settings['site_tagline'] = (string) ($settings['site_tagline'] ?: $defaults['site_tagline']);
        $settings['site_logo'] = $settings['site_logo'] ?: null;
        $settings['home_photos_count'] = max(1, (int) ($settings['home_photos_count'] ?? 12));
        $settings['gallery_grid_columns'] = max(1, min(4, (int) ($settings['gallery_grid_columns'] ?? 3)));
        $settings['grid_gap'] = in_array($settings['grid_gap'], ['sm', 'md', 'lg'], true)
            ? $settings['grid_gap']
            : 'md';

        return $settings;
    }

    public static function common(): array
    {
        $settings = static::settings();

        return [
            'siteName' => $settings['site_name'],
            'siteTagline' => $settings['site_tagline'],
            'siteLogo' => $settings['site_logo'],
            'heroImage' => $settings['hero_image'],
            'adminUser' => AdminSession::currentUser(),
            'menuPages' => static::menuPages(),
            'galleryTree' => static::galleryTree(),
            'settings' => $settings,
            'gridColumns' => $settings['gallery_grid_columns'],
            'galleryGap' => static::galleryGap($settings['grid_gap']),
        ];
    }

    public static function galleryGap(string $gap): string
    {
        return match ($gap) {
            'sm' => '0.75rem',
            'lg' => '1.35rem',
            default => '1rem',
        };
    }

    public static function menuPages(): Collection
    {
        return Page::query()
            ->published()
            ->visibleInMenu()
            ->orderBy('title')
            ->get(['id', 'title', 'slug']);
    }

    public static function galleryTree(): Collection
    {
        $allGalleries = Gallery::query()
            ->active()
            ->ordered()
            ->withCount('photos')
            ->get()
            ->groupBy(fn (Gallery $gallery) => $gallery->parent_id ?? 0);

        $buildBranch = function (int $parentId = 0) use (&$buildBranch, $allGalleries): Collection {
            return ($allGalleries[$parentId] ?? collect())
                ->map(function (Gallery $gallery) use (&$buildBranch) {
                    $gallery->setRelation('children', $buildBranch((int) $gallery->id));

                    return $gallery;
                })
                ->values();
        };

        return $buildBranch(0);
    }

    public static function galleryOptions(?Gallery $exclude = null): Collection
    {
        $allGalleries = Gallery::query()
            ->ordered()
            ->get()
            ->groupBy(fn (Gallery $gallery) => $gallery->parent_id ?? 0);

        $excludedIds = $exclude ? array_fill_keys(static::galleryBranchIds($exclude), true) : [];

        $buildBranch = function (int $parentId = 0, int $depth = 0) use (&$buildBranch, $allGalleries, $excludedIds): Collection {
            $items = collect();

            foreach (($allGalleries[$parentId] ?? collect()) as $gallery) {
                if (isset($excludedIds[$gallery->id])) {
                    continue;
                }

                $items->push((object) [
                    'id' => $gallery->id,
                    'label' => str_repeat('— ', $depth) . $gallery->display_name,
                    'depth' => $depth,
                    'gallery' => $gallery,
                ]);

                $items = $items->merge($buildBranch((int) $gallery->id, $depth + 1));
            }

            return $items;
        };

        return $buildBranch(0);
    }

    public static function latestPhotos(int $limit): Collection
    {
        return Photo::query()
            ->with(['gallery:id,slug,display_name'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    public static function topTags(int $limit = 12): Collection
    {
        return Tag::query()
            ->withCount('photos')
            ->orderByDesc('photos_count')
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    public static function galleryTags(Gallery $gallery, int $limit = 24): Collection
    {
        return Tag::query()
            ->whereHas('photos', fn ($query) => $query->where('gallery_id', $gallery->id))
            ->withCount([
                'photos' => fn ($query) => $query->where('gallery_id', $gallery->id),
            ])
            ->orderByDesc('photos_count')
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    public static function aboutPage(): Page
    {
        return Page::query()
            ->published()
            ->where('slug', 'about')
            ->firstOrFail();
    }

    public static function previewText(?string $html, int $limit = 120): string
    {
        return Str::limit(trim(strip_tags($html ?? '')), $limit);
    }

    private static function galleryBranchIds(Gallery $gallery): array
    {
        $gallery->loadMissing('childrenRecursive');

        $ids = [$gallery->id];

        foreach ($gallery->childrenRecursive as $child) {
            $ids = array_merge($ids, static::galleryBranchIds($child));
        }

        return array_values(array_unique($ids));
    }
}
