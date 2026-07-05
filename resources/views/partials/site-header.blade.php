@php
    $menuPages = $menuPages ?? collect();
    $galleryTree = $galleryTree ?? collect();
@endphp
<header class="site-header">
    <div class="container-xxl">
        <div class="site-header__inner">
            <div class="site-header__bar">
                <a href="{{ url('/') }}" class="site-brand" aria-label="{{ $siteName }}">
                    <span class="site-brand__mark">
                        @if(!empty($siteLogo))
                            <img src="{{ $siteLogo }}" alt="{{ $siteName }}">
                        @else
                            <span aria-hidden="true">636</span>
                        @endif
                    </span>
                    <span class="site-brand__text">
                        <span class="site-brand__title">{{ $siteName }}</span>
                        <span class="site-brand__subtitle">{{ $siteTagline }}</span>
                    </span>
                </a>

                <div class="site-controls">
                    <div class="translate-widget control-pill" id="google_translate_element"></div>

                    <button class="theme-toggle" type="button" data-theme-toggle aria-pressed="false">
                        <span aria-hidden="true">◐</span>
                        <span class="theme-toggle__label" data-theme-label>День</span>
                    </button>

                    <button
                        class="navbar-toggler d-lg-none"
                        type="button"
                        data-bs-toggle="offcanvas"
                        data-bs-target="#siteMenu"
                        aria-controls="siteMenu"
                        aria-label="Открыть меню"
                    >
                        <span class="navbar-toggler-icon"></span>
                    </button>
                </div>
            </div>

            <nav class="site-nav d-none d-lg-flex" aria-label="Основное меню">
                <a href="{{ url('/') }}" class="{{ request()->is('/') ? 'is-active' : '' }}">Главная</a>
                <a href="{{ url('/about') }}" class="{{ request()->is('about') ? 'is-active' : '' }}">Обо мне</a>
                @foreach($menuPages as $page)
                    <a href="{{ url('/page/' . $page->slug) }}">{{ $page->title }}</a>
                @endforeach
            </nav>
        </div>
    </div>

    <div class="offcanvas offcanvas-end" tabindex="-1" id="siteMenu" aria-labelledby="siteMenuLabel">
        <div class="offcanvas-header">
            <h2 class="offcanvas-title h4" id="siteMenuLabel">{{ $siteName }}</h2>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Закрыть"></button>
        </div>
        <div class="offcanvas-body">
            <div class="sidebar-panel">
                <div class="sidebar-card">
                    <div class="sidebar-card__title">Навигация</div>
                    <nav class="admin-nav">
                        <a href="{{ url('/') }}">Главная</a>
                        <a href="{{ url('/about') }}">Обо мне</a>
                        @foreach($menuPages as $page)
                            <a href="{{ url('/page/' . $page->slug) }}">{{ $page->title }}</a>
                        @endforeach
                    </nav>
                </div>

                <div class="sidebar-card">
                    <div class="sidebar-card__title">Каталоги</div>
                    <x-gallery-tree :items="$galleryTree" />
                </div>
            </div>
        </div>
    </div>
</header>
