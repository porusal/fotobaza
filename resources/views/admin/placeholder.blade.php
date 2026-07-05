@extends('layouts.admin')

@section('title', $title ?? 'В разработке')

@section('content')
    <section class="admin-card">
        <div class="panel__title">
            <div>
                <p class="eyebrow">Заглушка</p>
                <h2>{{ $title ?? 'Страница скоро появится' }}</h2>
            </div>
            @if(!empty($backUrl))
                <a class="btn-ghost" href="{{ $backUrl }}">Назад</a>
            @endif
        </div>

        <div class="empty-state text-start">
            <strong>{{ $message ?? 'На следующем этапе сюда подключим полный CRUD и обработку форм.' }}</strong>
            <span>Сейчас маршрут уже существует, чтобы не было 404 при навигации по админке.</span>
        </div>
    </section>
@endsection
