@php
    $rowIndex = $index ?? 0;
    $hasNumericIndex = is_numeric($rowIndex);
    $oldItems = old('items', []);
    $rowItem = $oldItems[$rowIndex] ?? [];
@endphp

<div class="upload-row" data-photo-upload-row>
    <div class="upload-row__header">
        <div>
            <strong>Фото @if($hasNumericIndex){{ $rowIndex + 1 }}@endif</strong>
            <span>Одна строка = один файл и свои теги</span>
        </div>

        <button type="button" class="btn-ghost" data-photo-remove-row>Удалить</button>
    </div>

    <div class="row g-3">
        <div class="col-lg-4">
            <label class="form-label">Файл</label>
            <input class="form-control" type="file" name="items[{{ $rowIndex }}][file]" accept="image/*" required>
            @error('items.' . $rowIndex . '.file')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-lg-4">
            <label class="form-label">Подпись / alt text</label>
            <input class="form-control" type="text" name="items[{{ $rowIndex }}][alt_text]" value="{{ $rowItem['alt_text'] ?? '' }}" placeholder="Краткое описание кадра">
            @error('items.' . $rowIndex . '.alt_text')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-lg-2">
            <label class="form-label">Порядок</label>
            <input class="form-control" type="number" min="0" max="99999" name="items[{{ $rowIndex }}][sort_order]" value="{{ $rowItem['sort_order'] ?? ($hasNumericIndex ? (($rowIndex + 1) * 10) : 10) }}">
            @error('items.' . $rowIndex . '.sort_order')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12">
            <label class="form-label">Теги</label>
            <select class="form-select" name="items[{{ $rowIndex }}][tags][]" multiple data-select2>
                @foreach(($tags ?? collect()) as $tag)
                    <option value="{{ $tag->id }}" @selected(in_array((string) $tag->id, array_map('strval', $rowItem['tags'] ?? []), true))>{{ $tag->name }}</option>
                @endforeach
            </select>
            @error('items.' . $rowIndex . '.tags')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>
