@extends('layouts.admin')

@section('title', 'Панель управления')

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
                <a class="next-action-card" href="{{ route('admin.galleries.create') }}">
                    <span class="next-action-card__icon" aria-hidden="true"><x-admin-icon name="folder" /></span>
                    <strong>Добавить каталоги</strong>
                    <span>Создавайте структуру альбомов и подкаталогов.</span>
                </a>
            </div>
            <div class="col-md-4">
                <a class="next-action-card" href="{{ route('admin.photos.create') }}">
                    <span class="next-action-card__icon" aria-hidden="true"><x-admin-icon name="image" /></span>
                    <strong>Загрузить фото</strong>
                    <span>Массовая загрузка с тегами и сортировкой.</span>
                </a>
            </div>
            <div class="col-md-4">
                <a class="next-action-card" href="{{ route('admin.settings.edit') }}">
                    <span class="next-action-card__icon" aria-hidden="true"><x-admin-icon name="settings" /></span>
                    <strong>Настроить сайт</strong>
                    <span>Логотип, девиз, сетка, цвета и шрифты.</span>
                </a>
            </div>
        </div>
    </section>
@endsection
