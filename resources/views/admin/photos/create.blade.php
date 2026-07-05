@extends('layouts.admin')

@section('title', 'Добавить фото')

@section('content')
    <form method="post" action="{{ route('admin.photos.store') }}" enctype="multipart/form-data" class="admin-card" id="photo-create-form">
        @csrf

        @if(session('status'))
            <div class="alert alert-success mb-3">{{ session('status') }}</div>
        @endif

        <div class="panel__title">
            <div>
                <p class="eyebrow">Фото</p>
                <h2>Массовая загрузка</h2>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a class="btn-ghost" href="{{ route('admin.photos.index') }}">Назад</a>
                <button type="button" class="btn-ghost" data-photo-add-row>Добавить строку</button>
                <button type="submit" class="btn-soft">Загрузить</button>
            </div>
        </div>

        <div class="admin-form-grid">
            <div class="row g-3">
                <div class="col-lg-6">
                    <label class="form-label" for="gallery_id">Каталог</label>
                    <select class="form-select @error('gallery_id') is-invalid @enderror" name="gallery_id" id="gallery_id">
                        <option value="">Выберите каталог</option>
                        @foreach(($galleries ?? collect()) as $gallery)
                            <option value="{{ $gallery->id }}" @selected((string) old('gallery_id') === (string) $gallery->id)>{{ $gallery->display_name }}</option>
                        @endforeach
                    </select>
                    @error('gallery_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-lg-6 d-flex align-items-end">
                    <div class="form-hint">
                        Каждая строка загружает отдельный файл с собственными тегами. Если нужно больше файлов, просто добавьте ещё одну строку.
                    </div>
                </div>

                <div class="col-12">
                    <div class="upload-list" data-photo-upload-list>
                        @foreach(old('items', [0 => []]) as $index => $row)
                            @include('admin.photos._row', ['index' => $index, 'tags' => $tags])
                        @endforeach
                    </div>
                    @error('items')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <template data-photo-upload-template>
            @include('admin.photos._row', ['index' => '__INDEX__', 'tags' => $tags])
        </template>
    </form>
@endsection
