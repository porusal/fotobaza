@php
    $tagFieldName = $name ?? 'tags[]';
    $selectedTagIds = collect($selected ?? [])
        ->map(fn ($id) => (string) $id)
        ->all();
@endphp

<div class="tag-checkbox-cloud">
    @forelse(($tags ?? collect()) as $tag)
        <label class="tag-checkbox">
            <input
                type="checkbox"
                name="{{ $tagFieldName }}"
                value="{{ $tag->id }}"
                @checked(in_array((string) $tag->id, $selectedTagIds, true))
            >
            <span>{{ $tag->name }}</span>
        </label>
    @empty
        <div class="empty-state empty-state--compact">
            Тегов пока нет.
            <span>Сначала создайте теги в разделе справочника.</span>
        </div>
    @endforelse
</div>
