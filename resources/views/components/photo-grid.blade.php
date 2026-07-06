@props([
    'photos' => collect(),
    'columns' => 3,
    'gap' => '1rem',
    'group' => 'gallery',
])

<div
    class="gallery-grid gallery-grid--{{ max(1, min((int) $columns, 4)) }}"
    data-lightgallery="true"
    style="--gallery-columns: {{ max(1, min((int) $columns, 4)) }}; --gallery-gap: {{ $gap }};"
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
