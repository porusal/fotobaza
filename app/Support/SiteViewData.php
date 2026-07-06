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
            'site_copyright' => '',
            'site_logo' => null,
            'hero_image' => null,
            'home_photos_count' => 12,
            'gallery_grid_columns_mobile' => 2,
            'gallery_grid_columns_tablet' => 3,
            'gallery_grid_columns' => 3,
            'grid_gap' => 'md',
            'hero_badge' => '',
            'intro_text' => 'Фотоистории с мягким светом, четкой композицией и легкой подачей для портфолио, брендов и личных проектов.',
            'translate_languages' => ['en'],
            'theme_text_color' => '#1c1712',
            'theme_heading_color' => '#1c1712',
            'theme_muted_color' => '#6e655d',
            'theme_accent_color' => '#a15f2d',
            'theme_accent_secondary_color' => '#2d6f67',
            'theme_accent_soft_color' => '#cf7158',
            'theme_tag_color' => '#2d6f67',
            'theme_dark_text_color' => '#f4efe8',
            'theme_dark_heading_color' => '#fff6ea',
            'theme_dark_muted_color' => '#cfc4b9',
            'theme_dark_accent_color' => '#ffb871',
            'theme_dark_accent_secondary_color' => '#7bcfc1',
            'theme_dark_accent_soft_color' => '#f28a73',
            'theme_dark_tag_color' => '#7bcfc1',
            'font_body' => 'manrope',
            'font_heading' => 'cormorant',
            'font_menu' => 'manrope',
            'font_catalog' => 'manrope',
            'font_tag' => 'manrope',
            'font_body_style' => 'normal',
            'font_heading_style' => 'bold',
            'font_menu_style' => 'bold',
            'font_catalog_style' => 'bold',
            'font_tag_style' => 'bold',
            'font_body_size' => '12pt',
            'font_heading_size' => '42pt',
            'font_menu_size' => '11pt',
            'font_catalog_size' => '12pt',
            'font_tag_size' => '11pt',
        ];

        $stored = Setting::query()->pluck('value', 'key')->all();
        $settings = array_merge($defaults, $stored);

        $settings['site_name'] = (string) ($settings['site_name'] ?: $defaults['site_name']);
        $settings['site_tagline'] = static::cleanSettingText($settings['site_tagline'] ?? '', $defaults['site_tagline']);
        $settings['site_copyright'] = static::cleanSettingText(
            $settings['site_copyright'] ?? '',
            '© ' . date('Y') . ' ' . $settings['site_name']
        );
        $settings['site_logo'] = $settings['site_logo'] ?: null;
        $settings['hero_badge'] = static::cleanSettingText($settings['hero_badge'] ?? '', $defaults['hero_badge'], true);
        $settings['intro_text'] = static::cleanSettingText($settings['intro_text'] ?? '', $defaults['intro_text']);
        $settings['home_photos_count'] = max(1, (int) ($settings['home_photos_count'] ?? 12));
        $settings['gallery_grid_columns_mobile'] = max(1, min(4, (int) ($settings['gallery_grid_columns_mobile'] ?? 2)));
        $settings['gallery_grid_columns_tablet'] = max(1, min(4, (int) ($settings['gallery_grid_columns_tablet'] ?? 3)));
        $settings['gallery_grid_columns'] = max(1, min(4, (int) ($settings['gallery_grid_columns'] ?? 3)));
        $settings['grid_gap'] = in_array($settings['grid_gap'], ['sm', 'md', 'lg'], true)
            ? $settings['grid_gap']
            : 'md';
        $settings['translate_languages'] = static::normalizeTranslationLanguages($settings['translate_languages'] ?? []);

        foreach (static::themeColorKeys() as $key) {
            $settings[$key] = static::normalizeHexColor($settings[$key] ?? $defaults[$key], $defaults[$key]);
        }

        foreach (static::themeFontKeys() as $key) {
            $settings[$key] = static::normalizeFontKey($settings[$key] ?? $defaults[$key], $defaults[$key]);
        }

        foreach (static::themeFontStyleKeys() as $key) {
            $settings[$key] = static::normalizeOptionKey($settings[$key] ?? $defaults[$key], $defaults[$key], static::themeFontStyleOptions());
        }

        foreach (static::themeFontSizeKeys() as $key) {
            $settings[$key] = static::normalizeOptionKey($settings[$key] ?? $defaults[$key], $defaults[$key], static::themeFontSizeOptions());
        }

        return $settings;
    }

    public static function common(): array
    {
        $settings = static::settings();

        return [
            'siteName' => $settings['site_name'],
            'siteTagline' => $settings['site_tagline'],
            'siteCopyright' => $settings['site_copyright'],
            'siteLogo' => $settings['site_logo'],
            'heroImage' => $settings['hero_image'],
            'adminUser' => AdminSession::currentUser(),
            'menuPages' => static::menuPages(),
            'galleryTree' => static::galleryTree(),
            'settings' => $settings,
            'gridColumns' => $settings['gallery_grid_columns'],
            'gridColumnsMobile' => $settings['gallery_grid_columns_mobile'],
            'gridColumnsTablet' => $settings['gallery_grid_columns_tablet'],
            'gridColumnsDesktop' => $settings['gallery_grid_columns'],
            'galleryGap' => static::galleryGap($settings['grid_gap']),
            'translationLanguageOptions' => static::translationLanguageOptions(),
            'translationLanguages' => $settings['translate_languages'],
            'currentTranslateLanguage' => static::currentTranslateLanguage($settings['translate_languages']),
            'siteThemeStyle' => static::themeStyle($settings),
            'themeFontOptions' => static::themeFontOptions(),
            'themeFontStyleOptions' => static::themeFontStyleOptions(),
            'themeFontSizeOptions' => static::themeFontSizeOptions(),
        ];
    }

    public static function themeColorKeys(): array
    {
        return [
            'theme_text_color',
            'theme_heading_color',
            'theme_muted_color',
            'theme_accent_color',
            'theme_accent_secondary_color',
            'theme_accent_soft_color',
            'theme_tag_color',
            'theme_dark_text_color',
            'theme_dark_heading_color',
            'theme_dark_muted_color',
            'theme_dark_accent_color',
            'theme_dark_accent_secondary_color',
            'theme_dark_accent_soft_color',
            'theme_dark_tag_color',
        ];
    }

    public static function themeFontKeys(): array
    {
        return [
            'font_body',
            'font_heading',
            'font_menu',
            'font_catalog',
            'font_tag',
        ];
    }

    public static function themeFontStyleKeys(): array
    {
        return [
            'font_body_style',
            'font_heading_style',
            'font_menu_style',
            'font_catalog_style',
            'font_tag_style',
        ];
    }

    public static function themeFontSizeKeys(): array
    {
        return [
            'font_body_size',
            'font_heading_size',
            'font_menu_size',
            'font_catalog_size',
            'font_tag_size',
        ];
    }

    public static function themeFontOptions(): array
    {
        return [
            'manrope' => ['label' => 'Manrope', 'stack' => '"Manrope", "Segoe UI", sans-serif'],
            'montserrat' => ['label' => 'Montserrat', 'stack' => '"Montserrat", "Segoe UI", sans-serif'],
            'raleway' => ['label' => 'Raleway', 'stack' => '"Raleway", "Segoe UI", sans-serif'],
            'nunito' => ['label' => 'Nunito Sans', 'stack' => '"Nunito Sans", "Segoe UI", sans-serif'],
            'open_sans' => ['label' => 'Open Sans', 'stack' => '"Open Sans", "Segoe UI", sans-serif'],
            'roboto' => ['label' => 'Roboto', 'stack' => '"Roboto", "Segoe UI", sans-serif'],
            'rubik' => ['label' => 'Rubik', 'stack' => '"Rubik", "Segoe UI", sans-serif'],
            'ubuntu' => ['label' => 'Ubuntu', 'stack' => '"Ubuntu", "Segoe UI", sans-serif'],
            'fira_sans' => ['label' => 'Fira Sans', 'stack' => '"Fira Sans", "Segoe UI", sans-serif'],
            'exo_2' => ['label' => 'Exo 2', 'stack' => '"Exo 2", "Segoe UI", sans-serif'],
            'arsenal' => ['label' => 'Arsenal', 'stack' => '"Arsenal", "Segoe UI", sans-serif'],
            'noto_sans' => ['label' => 'Noto Sans', 'stack' => '"Noto Sans", "Segoe UI", sans-serif'],
            'oswald' => ['label' => 'Oswald', 'stack' => '"Oswald", "Arial Narrow", sans-serif'],
            'cormorant' => ['label' => 'Cormorant Garamond', 'stack' => '"Cormorant Garamond", Georgia, serif'],
            'playfair' => ['label' => 'Playfair Display', 'stack' => '"Playfair Display", Georgia, serif'],
            'lora' => ['label' => 'Lora', 'stack' => '"Lora", Georgia, serif'],
            'merriweather' => ['label' => 'Merriweather', 'stack' => '"Merriweather", Georgia, serif'],
            'roboto_slab' => ['label' => 'Roboto Slab', 'stack' => '"Roboto Slab", Georgia, serif'],
            'bitter' => ['label' => 'Bitter', 'stack' => '"Bitter", Georgia, serif'],
            'spectral' => ['label' => 'Spectral', 'stack' => '"Spectral", Georgia, serif'],
            'libre_baskerville' => ['label' => 'Libre Baskerville', 'stack' => '"Libre Baskerville", Georgia, serif'],
            'noto_serif' => ['label' => 'Noto Serif', 'stack' => '"Noto Serif", Georgia, serif'],
            'source_serif' => ['label' => 'Source Serif 4', 'stack' => '"Source Serif 4", Georgia, serif'],
            'georgia' => ['label' => 'Georgia', 'stack' => 'Georgia, "Times New Roman", serif'],
            'trebuchet' => ['label' => 'Trebuchet MS', 'stack' => '"Trebuchet MS", "Segoe UI", sans-serif'],
        ];
    }

    public static function themeFontStyleOptions(): array
    {
        return [
            'normal' => ['label' => 'Обычный', 'style' => 'normal', 'weight' => '400'],
            'italic' => ['label' => 'Курсив', 'style' => 'italic', 'weight' => '400'],
            'bold' => ['label' => 'Жирный', 'style' => 'normal', 'weight' => '700'],
            'bold_italic' => ['label' => 'Жирный курсив', 'style' => 'italic', 'weight' => '700'],
        ];
    }

    public static function themeFontSizeOptions(): array
    {
        $options = [];

        foreach ([8, 9, 10, 11, 12, 13, 14, 15, 16, 18, 20, 22, 24, 28, 32, 36, 42, 48, 56, 64, 72] as $pt) {
            $options[$pt . 'pt'] = [
                'label' => $pt . ' pt',
                'body' => $pt . 'pt',
                'menu' => $pt . 'pt',
                'catalog' => $pt . 'pt',
                'tag' => $pt . 'pt',
                'h1' => $pt . 'pt',
                'h2' => max(10, (int) round($pt * 0.68)) . 'pt',
                'h3' => max(10, (int) round($pt * 0.52)) . 'pt',
                'h4' => max(10, (int) round($pt * 0.42)) . 'pt',
            ];
        }

        return $options;
    }

    public static function themeStyle(array $settings): string
    {
        $fontOptions = static::themeFontOptions();
        $styleOptions = static::themeFontStyleOptions();
        $sizeOptions = static::themeFontSizeOptions();

        $fontStack = static function (string $key) use ($settings, $fontOptions): string {
            $fontKey = (string) ($settings[$key] ?? 'manrope');

            return $fontOptions[$fontKey]['stack'] ?? $fontOptions['manrope']['stack'];
        };

        $fontStyle = static function (string $key, string $property) use ($settings, $styleOptions): string {
            $styleKey = (string) ($settings[$key] ?? 'normal');

            return $styleOptions[$styleKey][$property] ?? $styleOptions['normal'][$property];
        };

        $fontSize = static function (string $key, string $property, string $fallback = '12pt') use ($settings, $sizeOptions): string {
            $sizeKey = (string) ($settings[$key] ?? $fallback);

            return $sizeOptions[$sizeKey][$property] ?? $sizeOptions[$fallback][$property];
        };

        $variables = [
            '--theme-light-text' => $settings['theme_text_color'] ?? '#1c1712',
            '--theme-light-heading' => $settings['theme_heading_color'] ?? '#1c1712',
            '--theme-light-muted' => $settings['theme_muted_color'] ?? '#6e655d',
            '--theme-light-accent' => $settings['theme_accent_color'] ?? '#a15f2d',
            '--theme-light-accent-2' => $settings['theme_accent_secondary_color'] ?? '#2d6f67',
            '--theme-light-accent-3' => $settings['theme_accent_soft_color'] ?? '#cf7158',
            '--theme-light-tag' => $settings['theme_tag_color'] ?? '#2d6f67',
            '--theme-dark-text' => $settings['theme_dark_text_color'] ?? '#f4efe8',
            '--theme-dark-heading' => $settings['theme_dark_heading_color'] ?? '#fff6ea',
            '--theme-dark-muted' => $settings['theme_dark_muted_color'] ?? '#cfc4b9',
            '--theme-dark-accent' => $settings['theme_dark_accent_color'] ?? '#ffb871',
            '--theme-dark-accent-2' => $settings['theme_dark_accent_secondary_color'] ?? '#7bcfc1',
            '--theme-dark-accent-3' => $settings['theme_dark_accent_soft_color'] ?? '#f28a73',
            '--theme-dark-tag' => $settings['theme_dark_tag_color'] ?? '#7bcfc1',
            '--font-body' => $fontStack('font_body'),
            '--font-heading' => $fontStack('font_heading'),
            '--font-menu' => $fontStack('font_menu'),
            '--font-catalog' => $fontStack('font_catalog'),
            '--font-tag' => $fontStack('font_tag'),
            '--font-body-style' => $fontStyle('font_body_style', 'style'),
            '--font-body-weight' => $fontStyle('font_body_style', 'weight'),
            '--font-heading-style' => $fontStyle('font_heading_style', 'style'),
            '--font-heading-weight' => $fontStyle('font_heading_style', 'weight'),
            '--font-menu-style' => $fontStyle('font_menu_style', 'style'),
            '--font-menu-weight' => $fontStyle('font_menu_style', 'weight'),
            '--font-catalog-style' => $fontStyle('font_catalog_style', 'style'),
            '--font-catalog-weight' => $fontStyle('font_catalog_style', 'weight'),
            '--font-tag-style' => $fontStyle('font_tag_style', 'style'),
            '--font-tag-weight' => $fontStyle('font_tag_style', 'weight'),
            '--font-body-size' => $fontSize('font_body_size', 'body', '12pt'),
            '--font-menu-size' => $fontSize('font_menu_size', 'menu', '11pt'),
            '--font-catalog-size' => $fontSize('font_catalog_size', 'catalog', '12pt'),
            '--font-tag-size' => $fontSize('font_tag_size', 'tag', '11pt'),
            '--font-heading-h1-size' => $fontSize('font_heading_size', 'h1', '42pt'),
            '--font-heading-h2-size' => $fontSize('font_heading_size', 'h2', '42pt'),
            '--font-heading-h3-size' => $fontSize('font_heading_size', 'h3', '42pt'),
            '--font-heading-h4-size' => $fontSize('font_heading_size', 'h4', '42pt'),
        ];

        return collect($variables)
            ->map(fn (string $value, string $key): string => $key . ': ' . $value)
            ->implode('; ');
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

            if (! $current->parent) {
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
                    'label' => str_repeat('- ', $depth) . $gallery->display_name,
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

    private static function cleanSettingText(mixed $value, string $fallback, bool $allowEmpty = false): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return $allowEmpty ? '' : $fallback;
        }

        if (static::isLegacyText($value)) {
            return $allowEmpty ? '' : $fallback;
        }

        return $value;
    }

    private static function isLegacyText(string $value): bool
    {
        $legacyExactValues = [
            'Photography with atmosphere',
            'Modern photo gallery with atmosphere',
            'Now booking / editorial work',
            'Open for commissions / 2026',
        ];

        if (in_array($value, $legacyExactValues, true)) {
            return true;
        }

        $legacyFragments = [
            'РЎ',
            'Р¤',
            'Рџ',
            'Р ',
            'Рµ',
            'Р»',
            'СЃ',
            'С‚',
            'СЊ',
            'С‹',
            'вЂ',
            'В°',
        ];

        foreach ($legacyFragments as $fragment) {
            if (str_contains($value, $fragment)) {
                return true;
            }
        }

        return false;
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

    private static function normalizeHexColor(mixed $value, string $fallback): string
    {
        $value = strtolower(trim((string) $value));

        if (preg_match('/^#[0-9a-f]{6}$/', $value) === 1) {
            return $value;
        }

        return $fallback;
    }

    private static function normalizeFontKey(mixed $value, string $fallback): string
    {
        return static::normalizeOptionKey($value, $fallback, static::themeFontOptions());
    }

    private static function normalizeOptionKey(mixed $value, string $fallback, array $options): string
    {
        $value = trim((string) $value);

        if (array_key_exists($value, $options)) {
            return $value;
        }

        return $fallback;
    }
}
