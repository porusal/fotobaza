@extends('layouts.admin')

@section('title', 'Страницы')

@section('content')
    <section class="admin-card">
        <div class="panel__title">
            <div>
                <p class="eyebrow">CRUD</p>
                <h2>Информационные страницы</h2>
            </div>
            <a href="{{ route('admin.pages.create') }}" class="btn-soft">Новая страница</a>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Заголовок</th>
                        <th>Slug</th>
                        <th>Публикация</th>
                        <th>В меню</th>
                        <th class="text-end">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($pages ?? collect()) as $page)
                        <tr>
                            <td>
                                <strong>{{ $page->title }}</strong>
                                <div class="form-hint">{{ \Illuminate\Support\Str::limit(strip_tags($page->content ?? ''), 80) }}</div>
                            </td>
                            <td>{{ $page->slug }}</td>
                            <td>
                                <span class="chip {{ $page->is_published ? 'is-active' : '' }}">
                                    {{ $page->is_published ? 'Опубликована' : 'Черновик' }}
                                </span>
                            </td>
                            <td>
                                <span class="chip {{ $page->show_in_menu ? 'is-active' : '' }}">
                                    {{ $page->show_in_menu ? 'Да' : 'Нет' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex flex-wrap gap-2">
                                    <a class="btn-ghost" href="{{ route('admin.pages.edit', $page) }}">Редактировать</a>
                                    <a class="btn-ghost" href="{{ url('/page/' . $page->slug) }}" target="_blank" rel="noreferrer">Открыть</a>
                                    <form method="post" action="{{ route('admin.pages.destroy', $page) }}" onsubmit="return confirm('Удалить страницу?');">
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
                                <div class="empty-state">Страниц пока нет. Создайте «Обо мне» и любые информационные разделы.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
