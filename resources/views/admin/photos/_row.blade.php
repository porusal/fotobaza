@php
    $rowIndex = $index ?? 0;
    $hasNumericIndex = is_numeric($rowIndex);
    $oldItems = old('items', []);
    $rowItem = $oldItems[$rowIndex] ?? [];
    $fileInputId = 'photo-file-' . $rowIndex;
@endphp

<div class="upload-row" data-photo-upload-row>
    <div class="upload-row__header">
        <div>
            <strong>Фото @if($hasNumericIndex){{ $rowIndex + 1 }}@endif</strong>
            <span>Одна строка = один файл и свои теги</span>
        </div>

        <button type="button" class="btn-ghost icon-button" data-photo-remove-row title="Удалить строку" aria-label="Удалить строку">
            <x-admin-icon name="trash" />
        </button>
    </div>

    <div class="row g-3">
        <div class="col-lg-4">
            <label class="form-label">Файл</label>
            <div class="photo-upload-control" data-photo-file-control>
                <div class="photo-upload-picker">
                    <span class="btn-ghost photo-upload-button">
                        <x-admin-icon name="image" />
                        <span>Загрузить фото</span>
                    </span>
                    <input class="photo-upload-input" type="file" id="{{ $fileInputId }}" name="items[{{ $rowIndex }}][file]" accept="image/*" data-photo-file-input>
                </div>
                <div class="form-hint mt-2" data-photo-file-name data-empty-text="Файл не выбран">Файл не выбран</div>
            </div>
            <div class="form-hint mt-2">На смартфоне откроется системный выбор: камера или фото из галереи. Поддерживаются фото до 32 MB.</div>
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
            @include('admin.photos._tag-cloud', [
                'name' => 'items[' . $rowIndex . '][tags][]',
                'tags' => $tags,
                'selected' => $rowItem['tags'] ?? [],
            ])
            <div class="form-hint mt-2">Нет нужного тега? Откройте <a href="{{ route('admin.tags.index') }}">раздел тегов</a>.</div>
            @error('items.' . $rowIndex . '.tags')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>
