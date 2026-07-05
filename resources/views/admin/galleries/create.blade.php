@extends('layouts.admin')

@section('title', 'Создать каталог')

@section('content')
    <form method="post" action="{{ route('admin.galleries.store') }}" enctype="multipart/form-data" class="admin-card">
        @csrf

        @if(session('status'))
            <div class="alert alert-success mb-3">{{ session('status') }}</div>
        @endif

        <div class="panel__title">
            <div>
                <p class="eyebrow">Каталоги</p>
                <h2>Новый каталог</h2>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a class="btn-ghost" href="{{ route('admin.galleries.index') }}">Назад</a>
                <button type="submit" class="btn-soft">Создать</button>
            </div>
        </div>

        @include('admin.galleries._fields', ['gallery' => $gallery, 'parentOptions' => $parentOptions])
    </form>
@endsection
