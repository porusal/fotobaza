@extends('layouts.admin')

@section('title', 'Каталоги')

@section('content')
    <section class="admin-card">
        @if(session('status'))
            <div class="alert alert-success mb-3">{{ session('status') }}</div>
        @endif

        <div class="panel__title">
            <div>
                <p class="eyebrow">CRUD</p>
                <h2>Управление каталогами</h2>
            </div>
            <div class="d-flex flex-wrap gap-2 justify-content-end">
                <form method="post" action="{{ route('admin.galleries.sync-filesystem') }}" class="admin-inline-form">
                    @csrf
                    <button type="submit" class="btn-ghost">Синхронизировать FTP</button>
                </form>
                <a href="{{ route('admin.galleries.create') }}" class="btn-soft">Новый каталог</a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Название</th>
                        <th>Slug</th>
                        <th>Родитель</th>
                        <th>Статус</th>
                        <th class="text-end">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($galleries ?? collect()) as $gallery)
                        <tr>
                            <td>
                                <strong>{{ $gallery->display_name }}</strong>
                                <div class="form-hint">{{ $gallery->name }}</div>
                            </td>
                            <td>{{ $gallery->slug }}</td>
                            <td>{{ $gallery->parent?->display_name ?? '—' }}</td>
                            <td>
                                <span class="chip {{ $gallery->is_active ? 'is-active' : '' }}">
                                    {{ $gallery->is_active ? 'Активен' : 'Скрыт' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex flex-wrap gap-2">
                                    <a class="btn-ghost" href="{{ route('admin.galleries.edit', $gallery) }}">Редактировать</a>
                                    <a class="btn-ghost" href="{{ url('/gallery/' . $gallery->slug) }}" target="_blank" rel="noreferrer">Открыть</a>
                                    <form method="post" action="{{ route('admin.galleries.destroy', $gallery) }}" onsubmit="return confirm('Удалить каталог?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-ghost">Удалить</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    Каталогов пока нет.
                                    <span>Создайте корневые каталоги и подкаталоги, а затем добавьте cover_image и описание.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
