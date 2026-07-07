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
                    <button type="submit" class="btn-ghost">
                        <x-admin-icon name="sync" />
                        <span>Синхронизировать FTP</span>
                    </button>
                </form>
                <a href="{{ route('admin.galleries.create') }}" class="btn-soft">
                    <x-admin-icon name="plus" />
                    <span>Новый каталог</span>
                </a>
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
                            <td data-label="Название">
                                <strong>{{ $gallery->display_name }}</strong>
                                <div class="form-hint">{{ $gallery->name }}</div>
                            </td>
                            <td data-label="Slug">{{ $gallery->slug }}</td>
                            <td data-label="Родитель">{{ $gallery->parent?->display_name ?? '—' }}</td>
                            <td data-label="Статус">
                                <span class="status-icon {{ $gallery->is_active ? 'status-icon--success' : 'status-icon--muted' }}" title="{{ $gallery->is_active ? 'Активен' : 'Скрыт' }}" aria-label="{{ $gallery->is_active ? 'Активен' : 'Скрыт' }}">
                                    <x-admin-icon :name="$gallery->is_active ? 'check' : 'x'" />
                                </span>
                            </td>
                            <td class="text-end" data-label="Действия">
                                <div class="d-inline-flex flex-wrap gap-2">
                                    <a class="btn-ghost icon-button" href="{{ route('admin.galleries.edit', $gallery) }}" title="Редактировать" aria-label="Редактировать">
                                        <x-admin-icon name="edit" />
                                    </a>
                                    <a class="btn-ghost icon-button" href="{{ url('/gallery/' . $gallery->slug) }}" target="_blank" rel="noreferrer" title="Открыть" aria-label="Открыть">
                                        <x-admin-icon name="external" />
                                    </a>
                                    <form method="post" action="{{ route('admin.galleries.destroy', $gallery) }}" onsubmit="return confirm('Удалить каталог?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-ghost icon-button" title="Удалить" aria-label="Удалить">
                                            <x-admin-icon name="trash" />
                                        </button>
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
