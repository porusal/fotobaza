@php
    $siteName = $siteName ?? config('app.name', 'Foto 636');
    $siteLogo = $siteLogo ?? null;
    $siteTagline = $siteTagline ?? 'Фото-галерея с современным админ-интерфейсом.';
    $bodyClass = trim(($bodyClass ?? '') . ' layout-auth');
@endphp
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Вход — ' . $siteName)</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="{{ $bodyClass }}">
    <div class="auth-layout">
        <div class="auth-layout__orb auth-layout__orb--one"></div>
        <div class="auth-layout__orb auth-layout__orb--two"></div>

        <div class="auth-shell">
            <div class="auth-shell__panel">
                <div class="auth-shell__intro panel">
                    <a class="auth-brand" href="{{ route('home') }}">
                        <span class="site-brand__mark" aria-hidden="true">
                            @if(!empty($siteLogo))
                                <img src="{{ $siteLogo }}" alt="{{ $siteName }}">
                            @else
                                <span>636</span>
                            @endif
                        </span>
                        <span class="site-brand__text">
                            <strong class="site-brand__title">{{ $siteName }}</strong>
                            <span class="site-brand__subtitle">Фото-галерея</span>
                        </span>
                    </a>

                    <p class="eyebrow">Admin access</p>
                    <h1>@yield('title', 'Вход в панель управления')</h1>
                    <p class="page-copy">{{ $siteTagline }}</p>

                    <div class="auth-intro__chips" aria-hidden="true">
                        <span class="chip">LightGallery</span>
                        <span class="chip">SQLite</span>
                        <span class="chip">Google Authenticator</span>
                    </div>
                </div>

                <div class="auth-shell__card panel">
                    <div class="auth-layout__top">
                        <a class="btn-ghost" href="{{ route('home') }}">На сайт</a>
                        <button class="theme-toggle" type="button" data-theme-toggle aria-pressed="false">
                            <span aria-hidden="true">◐</span>
                            <span class="theme-toggle__label" data-theme-label>День</span>
                        </button>
                    </div>

                    @yield('content')
                </div>
            </div>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
