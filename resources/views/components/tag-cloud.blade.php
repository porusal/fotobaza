@props([
    'tags' => collect(),
    'baseUrl' => null,
    'activeTag' => null,
    'allUrl' => null,
])

<div class="tag-cloud">
    @if($allUrl)
        <a href="{{ $allUrl }}" class="tag-pill {{ $activeTag ? '' : 'is-active' }}">
            <span>{{ __('Все') }}</span>
        </a>
    @endif

    @forelse($tags as $tag)
        @php
            $tagUrl = $baseUrl ? $baseUrl . '?tag=' . urlencode($tag->name) : null;
        @endphp
        @if($tagUrl)
            <a href="{{ $tagUrl }}" class="tag-pill {{ $activeTag === $tag->name ? 'is-active' : '' }}">
                <span>{{ $tag->name }}</span>
                @if(isset($tag->photos_count))
                    <span class="tag-pill__count">{{ $tag->photos_count }}</span>
                @endif
            </a>
        @else
            <span class="tag-pill">
                <span>{{ $tag->name }}</span>
                @if(isset($tag->photos_count))
                    <span class="tag-pill__count">{{ $tag->photos_count }}</span>
                @endif
            </span>
        @endif
    @empty
        <div class="empty-state">{{ __('Облако тегов появится после загрузки фото.') }}</div>
    @endforelse
</div>
