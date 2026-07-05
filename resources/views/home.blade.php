@extends('layouts.app')

@section('title', $siteName . ' — главная')
@section('meta_description', $siteTagline)

@section('content')
    <div class="page-grid page-grid--sidebar">
        <aside class="page-sidebar d-none d-lg-grid">
            <div class="sidebar-panel">
                <div class="sidebar-card">
                    <div class="sidebar-card__title">Каталоги</div>
                    <x-gallery-tree :items="$galleryTree" />
                </div>

                <div class="sidebar-card">
                    <div class="sidebar-card__title">Популярные теги</div>
                    <x-tag-cloud :tags="$topTags" />
                </div>
            </div>
        </aside>

        <div class="page-content">
            <section class="hero">
                <div class="hero__panel">
                    <div class="hero__content">
                        <p class="eyebrow">Фотогалерея</p>
                        <h1>{{ $siteName }}</h1>
                        <p class="hero__lede">
                            {{ $introText ?? 'Лёгкий, современный и адаптивный сайт-галерея для портретов, съёмок и архивов.' }}
                        </p>

                        <div class="hero__actions">
                            <a class="btn-soft" href="#latest-photos">Смотреть фото</a>
                            <a class="btn-ghost" href="{{ url('/about') }}">О проекте</a>
                        </div>

                        <div class="hero__meta">
                            <div class="meta-card">
                                <strong>{{ $latestPhotosCount ?? 0 }}</strong>
                                <span>Фото на главной</span>
                            </div>
                            <div class="meta-card">
                                <strong>{{ $galleryCount ?? 0 }}</strong>
                                <span>Каталога в структуре</span>
                            </div>
                            <div class="meta-card">
                                <strong>{{ $tagCount ?? 0 }}</strong>
                                <span>Тегов в облаке</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="hero__visual">
                    <div class="hero-visual__frame">
                        @if(!empty($heroImage))
                            <img class="hero-visual__image" src="{{ $heroImage }}" alt="{{ $siteName }}">
                        @endif
                        <div class="hero-visual__badge">
                            {{ $heroBadge ?? 'Now booking / editorial work' }}
                        </div>
                        <div class="hero-visual__footer">
                            <div>
                                <span>Акцент</span>
                                <strong>{{ $siteTagline }}</strong>
                            </div>
                            <div>
                                <span>Формат</span>
                                <strong>Сетка, lightGallery, mobile-first</strong>
                            </div>
                        </div>
                    </div>

                    <div class="hero-aside">
                        <div class="hero-info">
                            <p>Сетка фото адаптируется под экран, а всплывающее окно lightGallery открывает снимки без лишнего шума.</p>
                        </div>
                        <div class="hero-info">
                            <p>Переключатель темы вешает класс <code>dark-mode</code> на <code>body</code>, чтобы весь интерфейс менял палитру через CSS-переменные.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="section" id="latest-photos">
                <div class="section-head">
                    <p class="eyebrow">Последние фото</p>
                    <div class="section-head__row">
                        <h2>Свежие кадры из последних съёмок</h2>
                        <div class="section-head__actions">
                            @foreach($topTags ?? collect() as $tag)
                                <span class="chip">{{ $tag->name }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>

                <x-photo-grid :photos="$latestPhotos" :columns="$gridColumns ?? 3" :gap="$galleryGap ?? '1rem'" />
            </section>
        </div>
    </div>
@endsection
