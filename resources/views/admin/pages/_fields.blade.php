<div class="row g-3">
    <div class="col-lg-6">
        <label class="form-label" for="title">Заголовок</label>
        <input class="form-control @error('title') is-invalid @enderror" type="text" name="title" id="title" value="{{ old('title', $page->title ?? '') }}" placeholder="Обо мне">
        @error('title')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6">
        <label class="form-label" for="slug">Slug</label>
        <input class="form-control @error('slug') is-invalid @enderror" type="text" name="slug" id="slug" value="{{ old('slug', $page->slug ?? '') }}" placeholder="about">
        <div class="form-hint">Можно оставить пустым, тогда slug будет собран автоматически.</div>
        @error('slug')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <x-rich-editor
            name="content"
            label="Содержимое"
            :value="$page->content ?? ''"
            placeholder="Текст страницы"
        />
    </div>

    <div class="col-lg-6">
        <label class="form-label" for="image">Изображение</label>
        <input class="form-control @error('image') is-invalid @enderror" type="file" name="image" id="image" accept="image/*">
        <div class="form-hint">Фото шапки для страницы или статьи.</div>
        @error('image')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror

        @if(!empty($page->image))
            <div class="mt-2 page-hero__figure" style="min-height: 180px;">
                <img src="{{ $page->image }}" alt="{{ $page->title ?? 'Страница' }}">
            </div>
        @endif
    </div>

    <div class="col-md-3 d-flex align-items-end">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_published" id="is_published" value="1" @checked(old('is_published', $page->is_published ?? true))>
            <label class="form-check-label" for="is_published">Опубликована</label>
        </div>
    </div>

    <div class="col-md-3 d-flex align-items-end">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="show_in_menu" id="show_in_menu" value="1" @checked(old('show_in_menu', $page->show_in_menu ?? true))>
            <label class="form-check-label" for="show_in_menu">Показывать в меню</label>
        </div>
    </div>
</div>
