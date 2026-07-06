@props([
    'photos' => collect(),
    'columns' => 3,
    'mobileColumns' => 2,
    'tabletColumns' => null,
    'desktopColumns' => null,
    'gap' => '1rem',
    'group' => 'gallery',
])

@php
    $desktopColumns = max(1, min((int) ($desktopColumns ?? $columns), 4));
    $tabletColumns = max(1, min((int) ($tabletColumns ?? $desktopColumns), 4));
    $mobileColumns = max(1, min((int) $mobileColumns, 4));
@endphp

<div
    class="gallery-grid gallery-grid--{{ $desktopColumns }}"
    data-lightgallery="true"
    style="--gallery-columns-mobile: {{ $mobileColumns }}; --gallery-columns-tablet: {{ $tabletColumns }}; --gallery-columns-desktop: {{ $desktopColumns }}; --gallery-columns: {{ $desktopColumns }}; --gallery-gap: {{ $gap }};"
>
    @forelse($photos as $photo)
        @php
            $title = $photo->alt_text ?: $photo->filename;
        @endphp

        <article class="photo-card {{ ($photo->orientation ?? null) === 'portrait' ? 'photo-card--portrait' : '' }}">
            <a
                class="photo-card__link lightgallery-item"
                href="{{ $photo->path }}"
                data-src="{{ $photo->path }}"
                data-thumb="{{ $photo->path }}"
                data-sub-html="<div class=&quot;lightGallery-captions&quot;><h4>{{ e($title) }}</h4></div>"
                aria-label="{{ $title }}"
            >
                <img
                    class="photo-card__image"
                    src="{{ $photo->path }}"
                    alt="{{ $title }}"
                    loading="lazy"
                >
                <span class="photo-card__veil" aria-hidden="true"></span>
                <span class="photo-card__caption">
                    <span>{{ __('Фото') }}</span>
                    <strong>{{ $title }}</strong>
                </span>
            </a>
        </article>
    @empty
        <div class="empty-state">{{ __('Пока нет фото для этого раздела.') }}</div>
    @endforelse
</div>
