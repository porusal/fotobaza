@php
    $menuPages = $menuPages ?? collect();
    $galleryTree = $galleryTree ?? collect();
@endphp

<div class="offcanvas offcanvas-end site-mobile-menu" tabindex="-1" id="siteMenu" aria-labelledby="siteMenuLabel">
    <div class="offcanvas-header">
        <h2 class="offcanvas-title h4" id="siteMenuLabel">{{ $siteName }}</h2>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="{{ __('Закрыть') }}"></button>
    </div>
    <div class="offcanvas-body">
        <div class="sidebar-panel">
            <div class="sidebar-card">
                <div class="sidebar-card__title">{{ __('Навигация') }}</div>
                <nav class="admin-nav">
                    <a href="{{ url('/') }}"><span>{{ __('Главная') }}</span></a>
                    <a href="{{ url('/about') }}"><span>{{ __('Обо мне') }}</span></a>
                    @foreach($menuPages as $page)
                        <a href="{{ url('/page/' . $page->slug) }}"><span>{{ $page->title }}</span></a>
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
