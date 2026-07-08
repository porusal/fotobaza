@php
    $siteName = $siteName ?? config('app.name', 'Foto 636');
    $siteLogo = $siteLogo ?? null;
    $bodyClass = trim(($bodyClass ?? '') . ' layout-admin');
    $adminNav = $adminNav ?? [
        ['label' => 'Панель управления', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard'],
        ['label' => 'Профиль', 'route' => 'admin.profile.edit', 'active' => 'admin.profile.*'],
        ['label' => 'Каталоги', 'route' => 'admin.galleries.index', 'active' => 'admin.galleries.*'],
        ['label' => 'Фото', 'route' => 'admin.photos.index', 'active' => 'admin.photos.*'],
        ['label' => 'Страницы', 'route' => 'admin.pages.index', 'active' => 'admin.pages.*'],
        ['label' => 'Теги', 'route' => 'admin.tags.index', 'active' => 'admin.tags.*'],
        ['label' => 'Настройки', 'route' => 'admin.settings.edit', 'active' => 'admin.settings.*'],
        ['label' => 'Безопасность', 'route' => 'admin.security.show', 'active' => 'admin.security.*'],
    ];
@endphp
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Админка — ' . $siteName)</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="{{ $bodyClass }}" @if(!empty($siteThemeStyle)) style="{{ $siteThemeStyle }}" @endif>
    <div class="admin-layout">
        <header class="admin-mobile-header">
            <button class="admin-mobile-header__menu" type="button" data-admin-menu-toggle aria-controls="admin-sidebar" aria-expanded="false" aria-label="Открыть меню">
                <x-admin-icon name="menu" />
            </button>
            <a class="admin-mobile-header__brand" href="{{ route('admin.dashboard') }}">
                @if(!empty($siteLogo))
                    <img src="{{ $siteLogo }}" alt="{{ $siteName }}">
                @else
                    <span>636</span>
                @endif
                <strong>@yield('title', $siteName)</strong>
            </a>
            <div class="admin-mobile-header__actions">
                @include('partials.language-switcher')
                <button class="theme-toggle" type="button" data-theme-toggle aria-pressed="false">
                    <span aria-hidden="true">◐</span>
                    <span class="theme-toggle__label" data-theme-label>День</span>
                </button>
            </div>
        </header>
        <div class="admin-menu-backdrop" data-admin-menu-close hidden></div>
        <div class="container-xxl">
            <div class="admin-shell">
                <aside class="admin-sidebar" id="admin-sidebar">
                    <button class="admin-sidebar__close" type="button" data-admin-menu-close aria-label="Закрыть меню">
                        <x-admin-icon name="x" />
                    </button>
                    <a class="admin-sidebar__brand site-brand" href="{{ route('admin.dashboard') }}">
                        <span class="site-brand__mark" aria-hidden="true">
                            @if(!empty($siteLogo))
                                <img src="{{ $siteLogo }}" alt="{{ $siteName }}">
                            @else
                                <span>636</span>
                            @endif
                        </span>
                        <span class="site-brand__text">
                            <strong class="site-brand__title">{{ $siteName }}</strong>
                            <span class="site-brand__subtitle">Панель управления</span>
                        </span>
                    </a>

                    <nav class="admin-nav" aria-label="Административная навигация">
                        @foreach($adminNav as $item)
                            <a
                                href="{{ route($item['route']) }}"
                                class="{{ request()->routeIs($item['active']) ? 'is-active' : '' }}"
                            >
                                <span>{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </nav>
                </aside>

                <section class="admin-content">
                    <div class="admin-toolbar">
                        <div class="admin-title">
                            <p class="eyebrow">Админка</p>
                            <h1>@yield('title', 'Управление сайтом')</h1>
                        </div>

                        <div class="admin-toolbar__actions">
                            @include('partials.language-switcher')
                            <button class="theme-toggle" type="button" data-theme-toggle aria-pressed="false">
                                <span aria-hidden="true">◐</span>
                                <span class="theme-toggle__label" data-theme-label>День</span>
                            </button>

                            @if(!empty($adminUser))
                                <div class="admin-userbox">
                                    <span class="admin-userbox__icon" aria-hidden="true">
                                        <x-admin-icon name="user" />
                                    </span>
                                    <strong>{{ $adminUser->name ?: 'Admin' }}</strong>
                                </div>

                                <form method="POST" action="{{ route('admin.logout') }}" class="admin-inline-form">
                                    @csrf
                                    <button class="btn-ghost" type="submit">Выход</button>
                                </form>
                            @endif
                        </div>
                    </div>

                    @yield('content')
                </section>
            </div>
        </div>
    </div>

    <script src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit" defer></script>
    @stack('scripts')
</body>
</html>
