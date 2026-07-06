@php
    $siteName = $siteName ?? config('app.name', 'Foto 636');
    $siteTagline = $siteTagline ?? 'Современная фотогалерея с атмосферой';
    $siteLogo = $siteLogo ?? null;
    $menuPages = $menuPages ?? collect();
    $galleryTree = $galleryTree ?? collect();
    $bodyClass = trim(($bodyClass ?? '') . ' layout-public');
@endphp
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $siteName)</title>
    <meta name="description" content="@yield('meta_description', __($siteTagline))">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="{{ $bodyClass }}" @if(!empty($siteThemeStyle)) style="{{ $siteThemeStyle }}" @endif>
    <a class="visually-skip" href="#main-content">{{ __('Перейти к содержанию') }}</a>
    <div class="site-shell">
        @include('partials.site-header', [
            'siteName' => $siteName,
            'siteTagline' => $siteTagline,
            'siteLogo' => $siteLogo,
            'menuPages' => $menuPages,
            'galleryTree' => $galleryTree,
        ])

        @include('partials.site-mobile-menu', [
            'siteName' => $siteName,
            'menuPages' => $menuPages,
            'galleryTree' => $galleryTree,
        ])

        <main id="main-content" class="layout-main">
            <div class="container-xxl">
                @yield('content')
            </div>
        </main>

        @include('partials.site-footer', [
            'siteName' => $siteName,
            'siteTagline' => $siteTagline,
        ])
    </div>

    <script src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit" defer></script>
    @stack('scripts')
</body>
</html>
