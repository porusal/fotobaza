@extends('layouts.admin')

@section('title', 'Редактировать фото')

@section('content')
    <div class="admin-card">
        @if(session('status'))
            <div class="alert alert-success mb-3">{{ session('status') }}</div>
        @endif

        <div class="panel__title">
            <div>
                <p class="eyebrow">Фото</p>
                <h2>{{ $photo->filename }}</h2>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a class="btn-ghost" href="{{ route('admin.photos.index') }}">Назад</a>
                <button type="submit" class="btn-soft icon-button" form="photo-edit-form" title="Сохранить" aria-label="Сохранить">
                    <x-admin-icon name="save" />
                </button>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-xl-8">
                <form method="post" action="{{ route('admin.photos.update', $photo) }}" enctype="multipart/form-data" class="admin-form-grid" id="photo-edit-form">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-lg-6">
                            <label class="form-label" for="gallery_id">Каталог</label>
                            <select class="form-select @error('gallery_id') is-invalid @enderror" name="gallery_id" id="gallery_id">
                                @foreach(($galleries ?? collect()) as $gallery)
                                    <option value="{{ $gallery->id }}" @selected((string) old('gallery_id', $photo->gallery_id) === (string) $gallery->id)>{{ $gallery->display_name }}</option>
                                @endforeach
                            </select>
                            @error('gallery_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-lg-6">
                            <label class="form-label" for="filename">Имя файла</label>
                            <input class="form-control @error('filename') is-invalid @enderror" type="text" name="filename" id="filename" value="{{ old('filename', $photo->filename) }}">
                            @error('filename')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-lg-6">
                            <label class="form-label" for="alt_text">Alt text</label>
                            <input class="form-control @error('alt_text') is-invalid @enderror" type="text" name="alt_text" id="alt_text" value="{{ old('alt_text', $photo->alt_text) }}">
                            @error('alt_text')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-lg-3">
                            <label class="form-label" for="sort_order">Порядок</label>
                            <input class="form-control @error('sort_order') is-invalid @enderror" type="number" min="0" max="99999" name="sort_order" id="sort_order" value="{{ old('sort_order', $photo->sort_order) }}">
                            @error('sort_order')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-lg-3">
                            <label class="form-label" for="photo_file">Заменить файл</label>
                            <div class="photo-upload-control" data-photo-file-control>
                                <label class="btn-soft photo-upload-button" for="photo_file">
                                    <x-admin-icon name="image" />
                                    <span>Выбрать файл</span>
                                </label>
                                <input class="photo-upload-input @error('photo_file') is-invalid @enderror" type="file" name="photo_file" id="photo_file" data-photo-file-input aria-label="Загрузить фото">
                                <div class="photo-upload-name" data-photo-file-name data-empty-text="Файл не выбран">Файл не выбран</div>
                            </div>
                            @error('photo_file')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Теги</label>
                            @include('admin.photos._tag-cloud', [
                                'name' => 'tags[]',
                                'tags' => $tags,
                                'selected' => old('tags', $photo->tags->pluck('id')->all()),
                            ])
                            <div class="form-hint mt-2">Нет нужного тега? Откройте <a href="{{ route('admin.tags.index') }}">раздел тегов</a>.</div>
                            @error('tags')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </form>
            </div>

            <div class="col-xl-4">
                <div class="admin-card h-100">
                    <p class="eyebrow">Превью</p>
                    <div class="folder-card__cover mb-3">
                        <img src="{{ $photo->path }}" alt="{{ $photo->alt_text ?: $photo->filename }}">
                    </div>
                    <div class="form-hint file-path">Текущий путь: <span>{{ $photo->path }}</span></div>
                    <div class="form-hint">Каталог: {{ $photo->gallery?->display_name ?? '-' }}</div>
                    <div class="form-hint">Теги: {{ $photo->tags->pluck('name')->join(', ') ?: 'Без тегов' }}</div>

                    <form method="post" action="{{ route('admin.photos.destroy', $photo) }}" class="mt-4" onsubmit="return confirm('Удалить фото?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-ghost w-100">
                            <x-admin-icon name="trash" />
                            <span>Удалить фото</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
