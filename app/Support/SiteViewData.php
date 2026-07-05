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
    private const TRANSLATION_SOURCE_LANGUAGE = 'ru';

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
            'hero_badge' => 'Сейчас открыта запись',
            'intro_text' => null,
            'translate_languages' => ['en'],
        ];

        $stored = Setting::query()->pluck('value', 'key')->all();
        $settings = array_merge($defaults, $stored);

        $settings['site_name'] = (string) ($settings['site_name'] ?: $defaults['site_name']);
        $settings['site_tagline'] = (string) ($settings['site_tagline'] ?: $defaults['site_tagline']);
        $settings['site_logo'] = $settings['site_logo'] ?: null;

        if (in_array($settings['site_tagline'], ['Photography with atmosphere', 'Modern photo gallery with atmosphere'], true)) {
            $settings['site_tagline'] = $defaults['site_tagline'];
        }

        $settings['hero_badge'] = (string) ($settings['hero_badge'] ?: $defaults['hero_badge']);

        if (in_array($settings['hero_badge'], ['Now booking / editorial work', 'Open for commissions / 2026'], true)) {
            $settings['hero_badge'] = $defaults['hero_badge'];
        }

        $settings['home_photos_count'] = max(1, (int) ($settings['home_photos_count'] ?? 12));
        $settings['gallery_grid_columns'] = max(1, min(4, (int) ($settings['gallery_grid_columns'] ?? 3)));
        $settings['grid_gap'] = in_array($settings['grid_gap'], ['sm', 'md', 'lg'], true)
            ? $settings['grid_gap']
            : 'md';
        $settings['translate_languages'] = static::normalizeTranslationLanguages($settings['translate_languages'] ?? []);

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
            'translationLanguageOptions' => static::translationLanguageOptions(),
            'translationLanguages' => $settings['translate_languages'],
            'currentTranslateLanguage' => static::currentTranslateLanguage($settings['translate_languages']),
        ];
    }

    public static function translationLanguageOptions(): array
    {
        return [
            ['code' => 'ru', 'label' => 'Русский', 'flag' => 'ru', 'is_source' => true],
            ['code' => 'en', 'label' => 'English', 'flag' => 'gb', 'is_source' => false],
            ['code' => 'et', 'label' => 'Estonian', 'flag' => 'ee', 'is_source' => false],
            ['code' => 'lt', 'label' => 'Lithuanian', 'flag' => 'lt', 'is_source' => false],
            ['code' => 'de', 'label' => 'German', 'flag' => 'de', 'is_source' => false],
            ['code' => 'fr', 'label' => 'French', 'flag' => 'fr', 'is_source' => false],
            ['code' => 'es', 'label' => 'Spanish', 'flag' => 'es', 'is_source' => false],
            ['code' => 'it', 'label' => 'Italian', 'flag' => 'it', 'is_source' => false],
            ['code' => 'pt', 'label' => 'Portuguese', 'flag' => 'pt', 'is_source' => false],
            ['code' => 'pl', 'label' => 'Polish', 'flag' => 'pl', 'is_source' => false],
            ['code' => 'uk', 'label' => 'Ukrainian', 'flag' => 'ua', 'is_source' => false],
            ['code' => 'be', 'label' => 'Belarusian', 'flag' => 'by', 'is_source' => false],
            ['code' => 'bg', 'label' => 'Bulgarian', 'flag' => 'bg', 'is_source' => false],
            ['code' => 'cs', 'label' => 'Czech', 'flag' => 'cz', 'is_source' => false],
            ['code' => 'sk', 'label' => 'Slovak', 'flag' => 'sk', 'is_source' => false],
            ['code' => 'sl', 'label' => 'Slovenian', 'flag' => 'si', 'is_source' => false],
            ['code' => 'hr', 'label' => 'Croatian', 'flag' => 'hr', 'is_source' => false],
            ['code' => 'sr', 'label' => 'Serbian', 'flag' => 'rs', 'is_source' => false],
            ['code' => 'ro', 'label' => 'Romanian', 'flag' => 'ro', 'is_source' => false],
            ['code' => 'hu', 'label' => 'Hungarian', 'flag' => 'hu', 'is_source' => false],
            ['code' => 'nl', 'label' => 'Dutch', 'flag' => 'nl', 'is_source' => false],
            ['code' => 'sv', 'label' => 'Swedish', 'flag' => 'se', 'is_source' => false],
            ['code' => 'no', 'label' => 'Norwegian', 'flag' => 'no', 'is_source' => false],
            ['code' => 'da', 'label' => 'Danish', 'flag' => 'dk', 'is_source' => false],
            ['code' => 'fi', 'label' => 'Finnish', 'flag' => 'fi', 'is_source' => false],
            ['code' => 'el', 'label' => 'Greek', 'flag' => 'gr', 'is_source' => false],
            ['code' => 'tr', 'label' => 'Turkish', 'flag' => 'tr', 'is_source' => false],
            ['code' => 'ar', 'label' => 'Arabic', 'flag' => 'sa', 'is_source' => false],
            ['code' => 'iw', 'label' => 'Hebrew', 'flag' => 'il', 'is_source' => false],
            ['code' => 'ja', 'label' => 'Japanese', 'flag' => 'jp', 'is_source' => false],
            ['code' => 'ko', 'label' => 'Korean', 'flag' => 'kr', 'is_source' => false],
            ['code' => 'hi', 'label' => 'Hindi', 'flag' => 'in', 'is_source' => false],
            ['code' => 'id', 'label' => 'Indonesian', 'flag' => 'id', 'is_source' => false],
            ['code' => 'vi', 'label' => 'Vietnamese', 'flag' => 'vn', 'is_source' => false],
            ['code' => 'th', 'label' => 'Thai', 'flag' => 'th', 'is_source' => false],
            ['code' => 'fa', 'label' => 'Persian', 'flag' => 'ir', 'is_source' => false],
            ['code' => 'ka', 'label' => 'Georgian', 'flag' => 'ge', 'is_source' => false],
            ['code' => 'hy', 'label' => 'Armenian', 'flag' => 'am', 'is_source' => false],
            ['code' => 'az', 'label' => 'Azerbaijani', 'flag' => 'az', 'is_source' => false],
            ['code' => 'kk', 'label' => 'Kazakh', 'flag' => 'kz', 'is_source' => false],
            ['code' => 'uz', 'label' => 'Uzbek', 'flag' => 'uz', 'is_source' => false],
            ['code' => 'ms', 'label' => 'Malay', 'flag' => 'my', 'is_source' => false],
            ['code' => 'lv', 'label' => 'Latviešu', 'flag' => 'lv', 'is_source' => false],
        ];
    }

    public static function translationTargetLanguageCodes(): array
    {
        return array_values(array_filter(
            array_column(static::translationLanguageOptions(), 'code'),
            fn (string $code) => $code !== self::TRANSLATION_SOURCE_LANGUAGE
        ));
    }

    public static function currentTranslateLanguage(array $enabledLanguages = []): string
    {
        $availableLanguages = array_values(array_unique(array_merge(
            [self::TRANSLATION_SOURCE_LANGUAGE],
            array_map(static fn ($language) => strtolower((string) $language), $enabledLanguages ?: static::translationTargetLanguageCodes())
        )));
        $language = strtolower((string) request()->cookie('site_locale', ''));

        if ($language === '') {
            $language = static::languageFromGoogleCookie((string) request()->cookie('googtrans', ''));
        }

        if (in_array($language, $availableLanguages, true)) {
            return $language;
        }

        return self::TRANSLATION_SOURCE_LANGUAGE;
    }

    private static function languageFromGoogleCookie(string $value): string
    {
        $parts = explode('/', trim($value, '/'));

        return strtolower((string) ($parts[1] ?? ''));
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
            ->where('slug', '!=', 'about')
            ->orderBy('title')
            ->get(['id', 'title', 'slug']);
    }

    public static function galleryOpenIds(Gallery $gallery): array
    {
        $ids = [$gallery->id];
        $current = $gallery;
        $guard = 0;

        while ($current->parent_id && $guard < 25) {
            $current->loadMissing('parent:id,parent_id,slug,display_name');

            if (!$current->parent) {
                break;
            }

            $ids[] = $current->parent->id;
            $current = $current->parent;
            $guard++;
        }

        return array_values(array_unique(array_reverse($ids)));
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

    public static function latestPhotosByTag(int $limit, ?string $tag = null): Collection
    {
        $query = Photo::query()
            ->with(['gallery:id,slug,display_name'])
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        if ($tag !== null && $tag !== '') {
            $query->whereHas('tags', fn ($tagQuery) => $tagQuery->where('name', $tag));
        }

        return $query->limit($limit)->get();
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

    private static function normalizeTranslationLanguages(mixed $value): array
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $value = $decoded;
            } else {
                $value = preg_split('/[,\s]+/', $value, -1, PREG_SPLIT_NO_EMPTY) ?: [];
            }
        }

        if (! is_array($value)) {
            return [];
        }

        $allowed = static::translationTargetLanguageCodes();
        $languages = [];

        foreach ($value as $language) {
            $language = strtolower(trim((string) $language));

            if ($language !== '' && in_array($language, $allowed, true)) {
                $languages[] = $language;
            }
        }

        return array_values(array_unique($languages));
    }
}
