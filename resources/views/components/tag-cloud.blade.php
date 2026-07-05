@props([
    'tags' => collect(),
    'baseUrl' => null,
    'activeTag' => null,
])

<div class="tag-cloud">
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
        <div class="empty-state">Облако тегов появится после загрузки фото.</div>
    @endforelse
</div>
