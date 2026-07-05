@extends('layouts.admin')

@section('title', 'Настройки сайта')

@section('content')
    <form method="post" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="admin-card">
        @csrf
        @method('PUT')

        @if(session('status'))
            <div class="alert alert-success mb-3">{{ session('status') }}</div>
        @endif

        <div class="panel__title">
            <div>
                <p class="eyebrow">Настройки</p>
                <h2>Основные параметры</h2>
            </div>
            <button type="submit" class="btn-soft">Сохранить</button>
        </div>

        <div class="admin-form-grid">
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
                            <img src="{{ $settings['hero_image'] }}" alt="Hero image">
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

                <div class="col-lg-12">
                    <label class="form-label" for="site_tagline">Девиз</label>
                    <input class="form-control @error('site_tagline') is-invalid @enderror" type="text" name="site_tagline" id="site_tagline" value="{{ old('site_tagline', $settings['site_tagline'] ?? '') }}">
                    @error('site_tagline')
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
                    <label class="form-label" for="grid_gap">Размер сетки</label>
                    <select class="form-select @error('grid_gap') is-invalid @enderror" name="grid_gap" id="grid_gap">
                        <option value="sm" @selected(old('grid_gap', $settings['grid_gap'] ?? 'md') === 'sm')>Small</option>
                        <option value="md" @selected(old('grid_gap', $settings['grid_gap'] ?? 'md') === 'md')>Medium</option>
                        <option value="lg" @selected(old('grid_gap', $settings['grid_gap'] ?? 'md') === 'lg')>Large</option>
                    </select>
                    @error('grid_gap')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <div class="admin-card admin-card--soft">
                        <div class="panel__title panel__title--compact">
                            <div>
                                <p class="eyebrow">Перевод</p>
                                <h3 class="h5 mb-0">Языки в выпадающем списке</h3>
                            </div>
                            <div class="form-hint mb-0">Русский показывается всегда как исходный.</div>
                        </div>

                        <div class="translation-picks">
                            @foreach(($translationLanguageOptions ?? collect()) as $language)
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
                    </div>
                </div>
            </div>

            <div class="admin-card mt-1">
                <p class="eyebrow">Примечание</p>
                <p>Изменения сохраняются в таблицу <code>settings</code> и сразу используются на публичной части сайта.</p>
            </div>
        </div>
    </form>
@endsection
