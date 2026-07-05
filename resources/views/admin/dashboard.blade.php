@extends('layouts.admin')

@section('title', 'Дашборд')

@section('content')
    <section class="admin-stat-grid">
        <article class="admin-stat-card">
            <p>Всего фото</p>
            <strong>{{ $photosCount ?? 0 }}</strong>
        </article>
        <article class="admin-stat-card">
            <p>Всего категорий</p>
            <strong>{{ $galleriesCount ?? 0 }}</strong>
        </article>
        <article class="admin-stat-card">
            <p>Всего страниц</p>
            <strong>{{ $pagesCount ?? 0 }}</strong>
        </article>
    </section>

    <section class="admin-card">
        <div class="panel__title">
            <div>
                <p class="eyebrow">Быстрый обзор</p>
                <h2>Что делать дальше</h2>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-4">
                <div class="empty-state">
                    Добавить каталоги
                    <span>Создавайте структуру альбомов и подкаталогов.</span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="empty-state">
                    Загрузить фото
                    <span>Массовая загрузка с тегами и сортировкой.</span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="empty-state">
                    Настроить сайт
                    <span>Логотип, девиз и сетка отображения.</span>
                </div>
            </div>
        </div>
    </section>
@endsection
