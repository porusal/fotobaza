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
        <article class="photo-card {{ ($photo->orientation ?? null) === 'portrait' ? 'photo-card--portrait' : '' }}">
            <a
                class="photo-card__link lightgallery-item"
                href="{{ $photo->path }}"
                data-src="{{ $photo->path }}"
                data-sub-html="{{ e($photo->alt_text ?: $photo->filename) }}"
                aria-label="{{ $photo->alt_text ?: $photo->filename }}"
            >
                <img
                    class="photo-card__image"
                    src="{{ $photo->path }}"
                    alt="{{ $photo->alt_text ?: $photo->filename }}"
                    loading="lazy"
                >
                <span class="photo-card__veil" aria-hidden="true"></span>
                <span class="photo-card__caption">
                    <span>Фото</span>
                    <strong>{{ $photo->alt_text ?: $photo->filename }}</strong>
                </span>
            </a>
        </article>
    @empty
        <div class="empty-state">Пока нет фото для этого раздела.</div>
    @endforelse
</div>
