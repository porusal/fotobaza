@php
    $sourceLanguage = 'ru';
    $translationLanguageOptions = collect($translationLanguageOptions ?? []);
    $translationLanguages = collect($translationLanguages ?? []);
    $currentTranslateLanguage = $currentTranslateLanguage ?? $sourceLanguage;
    $availableLanguages = $translationLanguageOptions->filter(function (array $language) use ($translationLanguages, $sourceLanguage): bool {
        return $language['code'] === $sourceLanguage || $translationLanguages->contains($language['code']);
    })->values();
    $includedLanguages = $translationLanguages->unique()->implode(',');
    $currentLanguage = $availableLanguages->firstWhere('code', $currentTranslateLanguage)
        ?? $availableLanguages->firstWhere('code', $sourceLanguage)
        ?? ['code' => $sourceLanguage, 'label' => 'Русский', 'flag' => 'ru'];
@endphp

<details
    class="language-switcher control-pill"
    data-language-switcher
    data-source-language="{{ $sourceLanguage }}"
    data-current-language="{{ $currentTranslateLanguage }}"
>
    <summary class="language-switcher__toggle" aria-label="{{ __('Выбор языка') }}">
        <span class="language-switcher__flag">
            <img data-language-current-flag src="{{ asset('flags/' . $currentLanguage['flag'] . '.svg') }}" alt="" aria-hidden="true">
        </span>
        <span class="language-switcher__current" data-language-current-label>{{ $currentLanguage['label'] }}</span>
        <span class="language-switcher__chevron" aria-hidden="true">⌄</span>
    </summary>

    <div class="language-switcher__menu" role="menu">
        @foreach($availableLanguages as $language)
            <a
                href="{{ route('language.switch', $language['code']) }}"
                class="language-switcher__item {{ $currentTranslateLanguage === $language['code'] ? 'is-active' : '' }}"
                role="menuitem"
                data-language-link
                data-language="{{ $language['code'] }}"
                data-language-label="{{ $language['label'] }}"
                data-language-flag-src="{{ asset('flags/' . $language['flag'] . '.svg') }}"
                @if($currentTranslateLanguage === $language['code']) aria-current="true" @endif
            >
                <img src="{{ asset('flags/' . $language['flag'] . '.svg') }}" alt="" aria-hidden="true">
                <span>{{ $language['label'] }}</span>
            </a>
        @endforeach
    </div>
</details>

<div
    id="google_translate_element"
    class="translate-widget"
    data-source-language="{{ $sourceLanguage }}"
    data-current-language="{{ $currentTranslateLanguage }}"
    data-included-languages="{{ $includedLanguages }}"
    aria-hidden="true"
></div>
