@extends('layouts.admin')

@section('title', 'Страницы')

@section('content')
    <section class="admin-card">
        <div class="panel__title">
            <div>
                <p class="eyebrow">CRUD</p>
                <h2>Информационные страницы</h2>
            </div>
            <a href="{{ route('admin.pages.create') }}" class="btn-soft">
                <x-admin-icon name="plus" />
                <span>Новая страница</span>
            </a>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Заголовок</th>
                        <th>Публикация</th>
                        <th>В меню</th>
                        <th class="text-end">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($pages ?? collect()) as $page)
                        <tr>
                            <td data-label="Заголовок">
                                <strong>{{ $page->title }}</strong>
                            </td>
                            <td data-label="Публикация">
                                <span class="status-icon {{ $page->is_published ? 'status-icon--success' : 'status-icon--muted' }}" title="{{ $page->is_published ? 'Опубликована' : 'Черновик' }}" aria-label="{{ $page->is_published ? 'Опубликована' : 'Черновик' }}">
                                    <x-admin-icon :name="$page->is_published ? 'eye' : 'eye-off'" />
                                </span>
                            </td>
                            <td data-label="В меню">
                                <span class="status-icon {{ $page->show_in_menu ? 'status-icon--success' : 'status-icon--muted' }}" title="{{ $page->show_in_menu ? 'Показывается в меню' : 'Не показывается в меню' }}" aria-label="{{ $page->show_in_menu ? 'Показывается в меню' : 'Не показывается в меню' }}">
                                    <x-admin-icon :name="$page->show_in_menu ? 'menu' : 'x'" />
                                </span>
                            </td>
                            <td class="text-end" data-label="Действия">
                                <div class="admin-actions-line">
                                    <a class="btn-ghost icon-button" href="{{ route('admin.pages.edit', $page) }}" title="Редактировать" aria-label="Редактировать">
                                        <x-admin-icon name="edit" />
                                    </a>
                                    <a class="btn-ghost icon-button" href="{{ url('/page/' . $page->slug) }}" target="_blank" rel="noreferrer" title="Открыть" aria-label="Открыть">
                                        <x-admin-icon name="external" />
                                    </a>
                                    <form method="post" action="{{ route('admin.pages.destroy', $page) }}" onsubmit="return confirm('Удалить страницу?');">
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
                            <td colspan="4">
                                <div class="empty-state">Страниц пока нет. Создайте «Обо мне» и любые информационные разделы.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
