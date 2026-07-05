@extends('layouts.admin')

@section('title', 'Создать страницу')

@section('content')
    <form method="post" action="{{ route('admin.pages.store') }}" enctype="multipart/form-data" class="admin-card">
        @csrf

        @if(session('status'))
            <div class="alert alert-success mb-3">{{ session('status') }}</div>
        @endif

        <div class="panel__title">
            <div>
                <p class="eyebrow">Страницы</p>
                <h2>Новая страница</h2>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a class="btn-ghost" href="{{ route('admin.pages.index') }}">Назад</a>
                <button type="submit" class="btn-soft">Создать</button>
            </div>
        </div>

        @include('admin.pages._fields', ['page' => $page])
    </form>
@endsection
