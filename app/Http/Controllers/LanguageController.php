<?php

namespace App\Http\Controllers;

use App\Support\SiteViewData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    private const SOURCE_LOCALE = 'ru';
    private const COOKIE_LIFETIME_MINUTES = 60 * 24 * 365;

    public function switch(Request $request, string $locale): RedirectResponse
    {
        $settings = SiteViewData::settings();
        $supportedLocales = array_values(array_unique(array_merge(
            [self::SOURCE_LOCALE],
            $settings['translate_languages'] ?? []
        )));

        $locale = strtolower($locale);

        if (! in_array($locale, $supportedLocales, true)) {
            $locale = self::SOURCE_LOCALE;
        }

        $response = redirect()
            ->back()
            ->withCookie(cookie('site_locale', $locale, self::COOKIE_LIFETIME_MINUTES, '/', null, null, false, false, 'lax'));

        if ($locale === self::SOURCE_LOCALE) {
            return $response->withCookie(cookie('googtrans', '', -1, '/', null, null, false, false, 'lax'));
        }

        return $response->withCookie(cookie('googtrans', '/' . self::SOURCE_LOCALE . '/' . $locale, self::COOKIE_LIFETIME_MINUTES, '/', null, null, false, false, 'lax'));
    }
}
