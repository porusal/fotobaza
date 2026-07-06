@extends('layouts.app')

@section('title', $siteName . ' - ' . __('Главная'))
@section('meta_description', __($siteTagline))

@section('content')
    <div class="page-grid page-grid--sidebar">
        <aside class="page-sidebar d-none d-lg-grid">
            <div class="sidebar-panel">
                <div class="sidebar-card">
                    <div class="sidebar-card__title">{{ __('Каталоги') }}</div>
                    <x-gallery-tree :items="$galleryTree" />
                </div>

                <div class="sidebar-card">
                    <div class="sidebar-card__title">{{ __('Теги этой категории') }}</div>
                    <x-tag-cloud :tags="$topTags" :base-url="url('/')" :all-url="url('/')" :active-tag="$activeTag ?? null" />
                </div>
            </div>
        </aside>

        <div class="page-content">
            <section class="hero">
                <div class="hero__panel">
                    <div class="hero__content">
                        <p class="eyebrow">{{ __('Фотогалерея') }}</p>
                        <h1>{{ $siteName }}</h1>
                        <p class="hero__lede">
                            {{ __($introText ?? 'Лёгкий, современный и адаптивный сайт-галерея для портретов, съёмок и архивов.') }}
                        </p>

                        <div class="hero__actions">
                            <a class="btn-soft" href="#latest-photos">{{ __('Смотреть фото') }}</a>
                            <a class="btn-ghost" href="{{ url('/about') }}">{{ __('О проекте') }}</a>
                        </div>

                        <div class="hero__meta">
                            <div class="meta-card">
                                <strong>{{ $latestPhotosCount ?? 0 }}</strong>
                                <span>{{ __('Фото на главной') }}</span>
                            </div>
                            <div class="meta-card">
                                <strong>{{ $galleryCount ?? 0 }}</strong>
                                <span>{{ __('Каталоги в структуре') }}</span>
                            </div>
                            <div class="meta-card">
                                <strong>{{ $tagCount ?? 0 }}</strong>
                                <span>{{ __('Тегов в облаке') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="hero__visual">
                    <div class="hero-visual__frame">
                        @if(!empty($heroImage))
                            <img class="hero-visual__image" src="{{ $heroImage }}" alt="{{ $siteName }}">
                        @endif

                        @if(!empty($heroBadge))
                            <div class="hero-visual__badge">{{ $heroBadge }}</div>
                        @endif
                    </div>
                </div>
            </section>

            <section class="section" id="latest-photos">
                <div class="section-head">
                    <p class="eyebrow">{{ __('Последние фото') }}</p>
                    <div class="section-head__stack">
                        <h2>{{ __('Свежие кадры из последних съёмок') }}</h2>
                        <div class="section-head__actions section-head__actions--cloud">
                            <x-tag-cloud :tags="$topTags" :base-url="url('/')" :all-url="url('/')" :active-tag="$activeTag ?? null" />
                        </div>
                    </div>
                </div>

                <x-photo-grid
                    :photos="$latestPhotos"
                    :columns="$gridColumns ?? 3"
                    :mobile-columns="$gridColumnsMobile ?? 2"
                    :tablet-columns="$gridColumnsTablet ?? 3"
                    :desktop-columns="$gridColumnsDesktop ?? ($gridColumns ?? 3)"
                    :gap="$galleryGap ?? '1rem'"
                />
            </section>
        </div>
    </div>
@endsection
