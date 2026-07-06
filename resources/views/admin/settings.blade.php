@extends('layouts.admin')

@section('title', 'Настройки сайта')

@php
    $colorGroups = [
        'Светлая тема' => [
            'theme_text_color' => 'Основной текст',
            'theme_heading_color' => 'Заголовки',
            'theme_muted_color' => 'Вторичный текст',
            'theme_accent_color' => 'Главный акцент',
            'theme_accent_secondary_color' => 'Второй акцент',
            'theme_accent_soft_color' => 'Мягкий акцент',
            'theme_tag_color' => 'Теги',
        ],
        'Тёмная тема' => [
            'theme_dark_text_color' => 'Основной текст',
            'theme_dark_heading_color' => 'Заголовки',
            'theme_dark_muted_color' => 'Вторичный текст',
            'theme_dark_accent_color' => 'Главный акцент',
            'theme_dark_accent_secondary_color' => 'Второй акцент',
            'theme_dark_accent_soft_color' => 'Мягкий акцент',
            'theme_dark_tag_color' => 'Теги',
        ],
    ];

    $fontFields = [
        'body' => 'Основной текст',
        'heading' => 'Заголовки',
        'menu' => 'Меню',
        'catalog' => 'Каталоги',
        'tag' => 'Теги',
    ];

    $themeFontOptions = $themeFontOptions ?? [];
    $themeFontStyleOptions = $themeFontStyleOptions ?? [];
    $themeFontSizeOptions = $themeFontSizeOptions ?? [];
@endphp

@section('content')
    <form method="post" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="admin-form-grid">
        @csrf
        @method('PUT')

        @if(session('status'))
            <div class="alert alert-success mb-0">{{ session('status') }}</div>
        @endif

        <section class="admin-card">
            <div class="panel__title">
                <div>
                    <p class="eyebrow">Настройки</p>
                    <h2>Основные параметры</h2>
                </div>
                <button type="submit" class="btn-soft">
                    <x-admin-icon name="save" />
                    <span>Сохранить</span>
                </button>
            </div>

            <div class="row g-3">
                <div class="col-lg-6">
                    <label class="form-label" for="site_logo">Логотип</label>
                    <input class="form-control @error('site_logo') is-invalid @enderror" type="file" name="site_logo" id="site_logo" accept="image/*">
                    <div class="form-hint">Используется в шапке сайта и в админке.</div>
                    @error('site_logo')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    @if(!empty($settings['site_logo']))
                        <div class="mt-2">
                            <img src="{{ $settings['site_logo'] }}" alt="Текущий логотип" style="max-height: 64px; width: auto;">
                        </div>
                    @endif
                </div>

                <div class="col-lg-6">
                    <label class="form-label" for="hero_image">Заглавная картинка</label>
                    <input class="form-control @error('hero_image') is-invalid @enderror" type="file" name="hero_image" id="hero_image" accept="image/*">
                    <div class="form-hint">Большая картинка для главного экрана.</div>
                    @error('hero_image')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    @if(!empty($settings['hero_image']))
                        <div class="mt-2 page-hero__figure" style="min-height: 180px;">
                            <img src="{{ $settings['hero_image'] }}" alt="Заглавная картинка">
                        </div>
                    @endif
                </div>

                <div class="col-lg-6">
                    <label class="form-label" for="site_name">Название сайта</label>
                    <input class="form-control @error('site_name') is-invalid @enderror" type="text" name="site_name" id="site_name" value="{{ old('site_name', $settings['site_name'] ?? '') }}">
                    @error('site_name')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-lg-6">
                    <label class="form-label" for="site_tagline">Девиз</label>
                    <input class="form-control @error('site_tagline') is-invalid @enderror" type="text" name="site_tagline" id="site_tagline" value="{{ old('site_tagline', $settings['site_tagline'] ?? '') }}">
                    @error('site_tagline')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-lg-6">
                    <label class="form-label" for="site_copyright">Копирайт в подвале</label>
                    <input class="form-control @error('site_copyright') is-invalid @enderror" type="text" name="site_copyright" id="site_copyright" maxlength="255" value="{{ old('site_copyright', $settings['site_copyright'] ?? '') }}">
                    <div class="form-hint">Этот текст выводится справа в футере. Например: © 2026 Foto 636.</div>
                    @error('site_copyright')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-lg-6">
                    <label class="form-label" for="hero_badge">Надпись на главной картинке</label>
                    <input class="form-control @error('hero_badge') is-invalid @enderror" type="text" name="hero_badge" id="hero_badge" maxlength="140" value="{{ old('hero_badge', $settings['hero_badge'] ?? '') }}">
                    <div class="form-hint">Оставьте поле пустым, чтобы поверх картинки ничего не выводилось.</div>
                    @error('hero_badge')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label class="form-label" for="intro_text">Вводный текст</label>
                    <textarea class="form-control @error('intro_text') is-invalid @enderror" name="intro_text" id="intro_text" rows="4">{{ old('intro_text', $settings['intro_text'] ?? '') }}</textarea>
                    @error('intro_text')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="home_photos_count">Фото на главной</label>
                    <input class="form-control @error('home_photos_count') is-invalid @enderror" type="number" min="1" max="50" name="home_photos_count" id="home_photos_count" value="{{ old('home_photos_count', $settings['home_photos_count'] ?? 12) }}">
                    @error('home_photos_count')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="gallery_grid_columns">Колонки сетки</label>
                    <select class="form-select @error('gallery_grid_columns') is-invalid @enderror" name="gallery_grid_columns" id="gallery_grid_columns">
                        @for($i = 1; $i <= 4; $i++)
                            <option value="{{ $i }}" @selected((int) old('gallery_grid_columns', $settings['gallery_grid_columns'] ?? 3) === $i)>{{ $i }}</option>
                        @endfor
                    </select>
                    @error('gallery_grid_columns')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="grid_gap">Размер промежутков</label>
                    <select class="form-select @error('grid_gap') is-invalid @enderror" name="grid_gap" id="grid_gap">
                        <option value="sm" @selected(old('grid_gap', $settings['grid_gap'] ?? 'md') === 'sm')>Маленький</option>
                        <option value="md" @selected(old('grid_gap', $settings['grid_gap'] ?? 'md') === 'md')>Средний</option>
                        <option value="lg" @selected(old('grid_gap', $settings['grid_gap'] ?? 'md') === 'lg')>Большой</option>
                    </select>
                    @error('grid_gap')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </section>

        <section class="admin-card admin-card--soft">
            <div class="panel__title panel__title--compact">
                <div>
                    <p class="eyebrow">Внешний вид</p>
                    <h2 class="h3 mb-0">Цветовая гамма</h2>
                    <p class="form-hint mb-0">Светлая и тёмная темы настраиваются отдельно.</p>
                </div>
                <button type="submit" class="btn-soft">
                    <x-admin-icon name="save" />
                    <span>Сохранить</span>
                </button>
            </div>

            <div class="theme-palette-grid">
                @foreach($colorGroups as $groupTitle => $colorFields)
                    <section class="theme-palette-card">
                        <h3 class="h4">{{ $groupTitle }}</h3>
                        <div class="theme-settings-grid">
                            @foreach($colorFields as $field => $label)
                                <label class="color-field" for="{{ $field }}">
                                    <span>{{ $label }}</span>
                                    <input
                                        class="form-control form-control-color @error($field) is-invalid @enderror"
                                        type="color"
                                        name="{{ $field }}"
                                        id="{{ $field }}"
                                        value="{{ old($field, $settings[$field] ?? '#1c1712') }}"
                                    >
                                    @error($field)
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </label>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        </section>

        <section class="admin-card admin-card--soft">
            <div class="panel__title panel__title--compact">
                <div>
                    <p class="eyebrow">Шрифты</p>
                    <h2 class="h3 mb-0">Google Fonts, стиль и размер</h2>
                    <p class="form-hint mb-0">Размеры выбираются в пунктах: 8 pt, 9 pt, 10 pt и так далее.</p>
                </div>
                <button type="submit" class="btn-soft">
                    <x-admin-icon name="save" />
                    <span>Сохранить</span>
                </button>
            </div>

            <div class="font-settings-list">
                @foreach($fontFields as $field => $label)
                    @php
                        $familyName = 'font_' . $field;
                        $styleName = 'font_' . $field . '_style';
                        $sizeName = 'font_' . $field . '_size';
                    @endphp
                    <section class="font-settings-row">
                        <h3 class="h4">{{ $label }}</h3>
                        <label>
                            <span>Шрифт</span>
                            <select class="form-select @error($familyName) is-invalid @enderror" name="{{ $familyName }}">
                                @foreach($themeFontOptions as $fontKey => $font)
                                    <option value="{{ $fontKey }}" @selected(old($familyName, $settings[$familyName] ?? 'manrope') === $fontKey)>
                                        {{ $font['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                        <label>
                            <span>Стиль</span>
                            <select class="form-select @error($styleName) is-invalid @enderror" name="{{ $styleName }}">
                                @foreach($themeFontStyleOptions as $styleKey => $style)
                                    <option value="{{ $styleKey }}" @selected(old($styleName, $settings[$styleName] ?? 'normal') === $styleKey)>
                                        {{ $style['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                        <label>
                            <span>Размер</span>
                            <select class="form-select @error($sizeName) is-invalid @enderror" name="{{ $sizeName }}">
                                @foreach($themeFontSizeOptions as $sizeKey => $size)
                                    <option value="{{ $sizeKey }}" @selected(old($sizeName, $settings[$sizeName] ?? '12pt') === $sizeKey)>
                                        {{ $size['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                    </section>
                @endforeach
            </div>
        </section>

        <section class="admin-card admin-card--soft">
            <div class="panel__title panel__title--compact">
                <div>
                    <p class="eyebrow">Перевод</p>
                    <h2 class="h3 mb-0">Языки в выпадающем списке</h2>
                    <p class="form-hint mb-0">Русский показывается всегда как исходный язык.</p>
                </div>
                <button type="submit" class="btn-soft">
                    <x-admin-icon name="save" />
                    <span>Сохранить</span>
                </button>
            </div>

            <div class="translation-picks">
                @foreach(($translationLanguageOptions ?? []) as $language)
                    @if($language['code'] === 'ru')
                        @continue
                    @endif

                    <label class="translation-pick">
                        <input
                            type="checkbox"
                            name="translate_languages[]"
                            value="{{ $language['code'] }}"
                            @checked(in_array($language['code'], old('translate_languages', $settings['translate_languages'] ?? []), true))
                        >
                        <span class="translation-pick__flag">
                            <img src="{{ asset('flags/' . $language['flag'] . '.svg') }}" alt="" aria-hidden="true">
                        </span>
                        <span class="translation-pick__label">{{ $language['label'] }}</span>
                    </label>
                @endforeach
            </div>

            @error('translate_languages')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </section>

        <section class="admin-card">
            <p class="eyebrow">Примечание</p>
            <p>Изменения сохраняются в таблице <code>settings</code> и сразу используются на публичной части сайта.</p>
        </section>
    </form>
@endsection
