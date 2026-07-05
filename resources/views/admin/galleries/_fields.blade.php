@php
    $selectedParentId = old('parent_id', $gallery->parent_id ?? null);
@endphp

<div class="row g-3">
    <div class="col-lg-6">
        <label class="form-label" for="parent_id">Родительский каталог</label>
        <select class="form-select @error('parent_id') is-invalid @enderror" name="parent_id" id="parent_id">
            <option value="">Корневой каталог</option>
            @foreach(($parentOptions ?? collect()) as $option)
                <option value="{{ $option->id }}" @selected((string) $selectedParentId === (string) $option->id)>
                    {{ $option->label }}
                </option>
            @endforeach
        </select>
        @error('parent_id')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6">
        <label class="form-label" for="name">Системное имя</label>
        <input class="form-control @error('name') is-invalid @enderror" type="text" name="name" id="name" value="{{ old('name', $gallery->name ?? '') }}" placeholder="portraits">
        @error('name')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6">
        <label class="form-label" for="display_name">Отображаемое имя</label>
        <input class="form-control @error('display_name') is-invalid @enderror" type="text" name="display_name" id="display_name" value="{{ old('display_name', $gallery->display_name ?? '') }}" placeholder="Portraits">
        @error('display_name')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6">
        <label class="form-label" for="slug">Slug</label>
        <input class="form-control @error('slug') is-invalid @enderror" type="text" name="slug" id="slug" value="{{ old('slug', $gallery->slug ?? '') }}" placeholder="portraits">
        <div class="form-hint">Если оставить пустым, slug будет собран автоматически.</div>
        @error('slug')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <x-rich-editor
            name="description"
            label="Описание"
            :value="$gallery->description ?? ''"
            placeholder="Текст для страницы каталога"
        />
    </div>

    <div class="col-lg-6">
        <label class="form-label" for="cover_image">Обложка каталога</label>
        <input class="form-control @error('cover_image') is-invalid @enderror" type="file" name="cover_image" id="cover_image" accept="image/*">
        <div class="form-hint">Используется как крупная картинка в шапке каталога.</div>
        @error('cover_image')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror

        @if(!empty($gallery->cover_image))
            <div class="mt-2 folder-card__cover">
                <img src="{{ $gallery->cover_image }}" alt="{{ $gallery->display_name ?? 'Каталог' }}">
            </div>
        @endif
    </div>

    <div class="col-md-3">
        <label class="form-label" for="sort_order">Порядок</label>
        <input class="form-control @error('sort_order') is-invalid @enderror" type="number" min="0" max="99999" name="sort_order" id="sort_order" value="{{ old('sort_order', $gallery->sort_order ?? 0) }}">
        @error('sort_order')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3 d-flex align-items-end">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', $gallery->is_active ?? true))>
            <label class="form-check-label" for="is_active">Активен</label>
        </div>
    </div>
</div>
