@extends('layouts.admin')

@section('title', 'Теги')

@section('content')
    <section class="admin-card">
        @if(session('status'))
            <div class="alert alert-success mb-3">{{ session('status') }}</div>
        @endif

        <div class="panel__title">
            <div>
                <p class="eyebrow">Справочник</p>
                <h2>Управление тегами</h2>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-4">
                <form method="post" action="{{ route('admin.tags.store') }}" class="admin-card h-100">
                    @csrf

                    <label class="form-label" for="tag_name">Новый тег</label>
                    <input class="form-control @error('name') is-invalid @enderror" type="text" name="name" id="tag_name" value="{{ old('name') }}" placeholder="Например: portrait">
                    @error('name')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror

                    <div class="form-hint mt-3">После создания тег появится в списках выбора для фото.</div>

                    <button type="submit" class="btn-soft mt-3">
                        <x-admin-icon name="plus" />
                        <span>Добавить тег</span>
                    </button>
                </form>
            </div>

            <div class="col-lg-8">
                <div class="admin-card h-100">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Название</th>
                                    <th>Фото</th>
                                    <th class="text-end">Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tags as $tag)
                                    <tr>
                                        <td class="w-100" data-label="Название">
                                            <form method="post" action="{{ route('admin.tags.update', $tag) }}" class="d-flex flex-column flex-lg-row gap-2 align-items-lg-center">
                                                @csrf
                                                @method('PUT')

                                                <input class="form-control form-control-sm @error('name') is-invalid @enderror" type="text" name="name" value="{{ old('name', $tag->name) }}">

                                                <button type="submit" class="btn-ghost icon-button" title="Сохранить" aria-label="Сохранить">
                                                    <x-admin-icon name="save" />
                                                </button>
                                            </form>
                                        </td>
                                        <td data-label="Фото">
                                            <span class="chip">{{ $tag->photos_count }}</span>
                                        </td>
                                        <td class="text-end" data-label="Действия">
                                            <form method="post" action="{{ route('admin.tags.destroy', $tag) }}" onsubmit="return confirm('Удалить тег?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-ghost icon-button" title="Удалить" aria-label="Удалить">
                                                    <x-admin-icon name="trash" />
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3">
                                            <div class="empty-state">
                                                Тегов пока нет.
                                                <span>Создайте первый тег через форму слева.</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
