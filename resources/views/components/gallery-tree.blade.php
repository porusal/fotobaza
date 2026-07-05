@props([
    'items' => collect(),
    'activeSlug' => null,
    'openIds' => [],
    'depth' => 0,
])

@php
    $openIds = collect($openIds)->map(fn ($id) => (int) $id)->all();
@endphp

<ul class="gallery-tree {{ $depth > 0 ? 'gallery-tree--nested' : '' }}">
    @forelse($items as $item)
        @php
            $hasChildren = $item->relationLoaded('children') && $item->children->isNotEmpty();
            $isOpen = $hasChildren && in_array((int) $item->id, $openIds, true);
        @endphp
        <li class="gallery-tree__item" data-gallery-tree-item>
            <div class="gallery-tree__row">
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

                @if($hasChildren)
                    <button
                        type="button"
                        class="gallery-tree__toggle"
                        data-gallery-tree-toggle
                        data-title-expanded="{{ __('Свернуть ветку') }}"
                        data-title-collapsed="{{ __('Развернуть ветку') }}"
                        aria-expanded="{{ $isOpen ? 'true' : 'false' }}"
                        aria-controls="gallery-tree-children-{{ $item->id }}"
                        title="{{ $isOpen ? __('Свернуть ветку') : __('Развернуть ветку') }}"
                    >
                        <span class="gallery-tree__chevron" aria-hidden="true">></span>
                    </button>
                @endif
            </div>

            @if($hasChildren)
                <div
                    class="gallery-tree__children"
                    id="gallery-tree-children-{{ $item->id }}"
                    data-gallery-tree-children
                    @unless($isOpen) hidden @endunless
                >
                    <x-gallery-tree :items="$item->children" :active-slug="$activeSlug" :open-ids="$openIds" :depth="$depth + 1" />
                </div>
            @endif
        </li>
    @empty
        <li class="gallery-tree__item">
            <div class="empty-state">{{ __('Каталоги появятся после добавления данных.') }}</div>
        </li>
    @endforelse
</ul>
