@extends('layouts.admin')

@section('title', 'Редактировать каталог')

@section('content')
    <div class="admin-card">
        @if(session('status'))
            <div class="alert alert-success mb-3">{{ session('status') }}</div>
        @endif

        <form method="post" action="{{ route('admin.galleries.update', $gallery) }}" enctype="multipart/form-data" class="admin-form-grid" id="gallery-edit-form">
            @csrf
            @method('PUT')

            <div class="panel__title">
                <div>
                    <p class="eyebrow">Каталоги</p>
                    <h2>{{ $gallery->display_name }}</h2>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn-ghost" href="{{ route('admin.galleries.index') }}">Назад</a>
                    <a class="btn-ghost icon-button" href="{{ url('/gallery/' . $gallery->slug) }}" target="_blank" rel="noreferrer" title="Открыть" aria-label="Открыть">
                        <x-admin-icon name="external" />
                    </a>
                    <button type="submit" class="btn-soft icon-button" title="Сохранить" aria-label="Сохранить">
                        <x-admin-icon name="save" />
                    </button>
                </div>
            </div>

            @include('admin.galleries._fields', ['gallery' => $gallery, 'parentOptions' => $parentOptions])
        </form>

        <div class="d-flex flex-wrap gap-2 mt-4">
            <form method="post" action="{{ route('admin.galleries.destroy', $gallery) }}" onsubmit="return confirm('Удалить каталог и освободить вложенные фото?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-ghost icon-button" title="Удалить" aria-label="Удалить">
                    <x-admin-icon name="trash" />
                </button>
            </form>
        </div>
    </div>
@endsection
