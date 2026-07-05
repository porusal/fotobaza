@extends('layouts.admin')

@section('title', 'Редактировать страницу')

@section('content')
    <div class="admin-card">
        @if(session('status'))
            <div class="alert alert-success mb-3">{{ session('status') }}</div>
        @endif

        <form method="post" action="{{ route('admin.pages.update', $page) }}" enctype="multipart/form-data" class="admin-form-grid" id="page-edit-form">
            @csrf
            @method('PUT')

            <div class="panel__title">
                <div>
                    <p class="eyebrow">Страницы</p>
                    <h2>{{ $page->title }}</h2>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn-ghost" href="{{ route('admin.pages.index') }}">Назад</a>
                    <a class="btn-ghost" href="{{ url('/page/' . $page->slug) }}" target="_blank" rel="noreferrer">Открыть</a>
                    <button type="submit" class="btn-soft">Сохранить</button>
                </div>
            </div>

            @include('admin.pages._fields', ['page' => $page])
        </form>

        <div class="d-flex flex-wrap gap-2 mt-4">
            <form method="post" action="{{ route('admin.pages.destroy', $page) }}" onsubmit="return confirm('Удалить страницу?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-ghost">Удалить</button>
            </form>
        </div>
    </div>
@endsection
