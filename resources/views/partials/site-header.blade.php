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
                        <span class="site-brand__subtitle">{{ __($siteTagline) }}</span>
                    </span>
                </a>

                <div class="site-controls">
                    @include('partials.language-switcher')

                    <button
                        class="theme-toggle"
                        type="button"
                        data-theme-toggle
                        data-theme-label-light="{{ __('День') }}"
                        data-theme-label-dark="{{ __('Ночь') }}"
                        aria-pressed="false"
                    >
                        <span aria-hidden="true">◐</span>
                        <span class="theme-toggle__label" data-theme-label>{{ __('День') }}</span>
                    </button>

                    <button
                        class="navbar-toggler d-lg-none"
                        type="button"
                        data-bs-toggle="offcanvas"
                        data-bs-target="#siteMenu"
                        aria-controls="siteMenu"
                        aria-label="{{ __('Открыть меню') }}"
                    >
                        <span class="navbar-toggler-icon"></span>
                    </button>
                </div>
            </div>

            <nav class="site-nav d-none d-lg-flex" aria-label="{{ __('Навигация') }}">
                <a href="{{ url('/') }}" class="{{ request()->is('/') ? 'is-active' : '' }}">{{ __('Главная') }}</a>
                <a href="{{ url('/about') }}" class="{{ request()->is('about') ? 'is-active' : '' }}">{{ __('Обо мне') }}</a>
                @foreach($menuPages as $page)
                    <a href="{{ url('/page/' . $page->slug) }}">{{ $page->title }}</a>
                @endforeach
            </nav>
        </div>
    </div>

    <div class="offcanvas offcanvas-end" tabindex="-1" id="siteMenu" aria-labelledby="siteMenuLabel">
        <div class="offcanvas-header">
            <h2 class="offcanvas-title h4" id="siteMenuLabel">{{ $siteName }}</h2>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="{{ __('Закрыть') }}"></button>
        </div>
        <div class="offcanvas-body">
            <div class="sidebar-panel">
                <div class="sidebar-card">
                    <div class="sidebar-card__title">{{ __('Навигация') }}</div>
                    <nav class="admin-nav">
                        <a href="{{ url('/') }}">{{ __('Главная') }}</a>
                        <a href="{{ url('/about') }}">{{ __('Обо мне') }}</a>
                        @foreach($menuPages as $page)
                            <a href="{{ url('/page/' . $page->slug) }}">{{ $page->title }}</a>
                        @endforeach
                    </nav>
                </div>

                <div class="sidebar-card">
                    <div class="sidebar-card__title">{{ __('Каталоги') }}</div>
                    <x-gallery-tree :items="$galleryTree" />
                </div>
            </div>
        </div>
    </div>
</header>
