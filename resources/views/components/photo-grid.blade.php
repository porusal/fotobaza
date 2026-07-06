@props([
    'photos' => collect(),
    'columns' => 3,
    'gap' => '1rem',
    'group' => 'gallery',
])

@php
    $photos = collect($photos);
    $carouselId = 'photo-carousel-' . md5($group . '-' . $photos->pluck('id')->join('-'));
    $carouselItems = $photos->map(function ($photo) {
        $title = $photo->alt_text ?: $photo->filename;

        return [
            'src' => $photo->path,
            'thumb' => $photo->path,
            'subHtml' => '<div class="lightGallery-captions"><h4>' . e($title) . '</h4></div>',
        ];
    })->values()->all();
    $carouselJson = json_encode(
        $carouselItems,
        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
@endphp

@if($photos->isNotEmpty())
    <div
        class="photo-carousel-shell"
        data-lightgallery-carousel-shell
        style="--gallery-columns: {{ max(1, min((int) $columns, 4)) }}; --gallery-gap: {{ $gap }};"
    >
        <div
            id="{{ $carouselId }}"
            class="photo-carousel"
            data-lightgallery-carousel
            aria-label="{{ __('Фотогалерея') }}"
        ></div>
        <script type="application/json" data-lightgallery-items>{!! $carouselJson !!}</script>
    </div>
@else
    <div class="empty-state">{{ __('Пока нет фото для этого раздела.') }}</div>
@endif
