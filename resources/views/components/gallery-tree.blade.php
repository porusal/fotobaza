@props([
    'items' => collect(),
    'activeSlug' => null,
    'depth' => 0,
])

<ul class="gallery-tree {{ $depth > 0 ? 'gallery-tree--nested' : '' }}">
    @forelse($items as $item)
        <li class="gallery-tree__item">
            <a
                href="{{ url('/gallery/' . $item->slug) }}"
                class="gallery-tree__link {{ $activeSlug === $item->slug ? 'is-active' : '' }}"
                @if($activeSlug === $item->slug) aria-current="page" @endif
            >
                <span>{{ $item->display_name }}</span>
                @if(isset($item->photos_count))
                    <small>{{ $item->photos_count }}</small>
                @endif
            </a>

            @if($item->relationLoaded('children') && $item->children->isNotEmpty())
                <x-gallery-tree :items="$item->children" :active-slug="$activeSlug" :depth="$depth + 1" />
            @endif
        </li>
    @empty
        <li class="gallery-tree__item">
            <div class="empty-state">Каталоги появятся после добавления данных.</div>
        </li>
    @endforelse
</ul>
