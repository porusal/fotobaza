@extends('layouts.admin')

@section('title', 'Фото')

@section('content')
    <section class="admin-card">
        @if(session('status'))
            <div class="alert alert-success mb-3">{{ session('status') }}</div>
        @endif

        <div class="panel__title">
            <div>
                <p class="eyebrow">Медиа</p>
                <h2>Загруженные фото</h2>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('admin.photos.create') }}" class="btn-soft">Добавить фото</a>
            </div>
        </div>

        <div class="row g-3">
            @forelse(($photos ?? collect()) as $photo)
                <div class="col-md-6 col-xl-4">
                    <article class="admin-card h-100">
                        <div class="folder-card__cover mb-3">
                            <img src="{{ $photo->path }}" alt="{{ $photo->alt_text ?: $photo->filename }}">
                        </div>

                        <strong class="d-block">{{ $photo->filename }}</strong>
                        <div class="form-hint">{{ $photo->gallery?->display_name ?? 'Без каталога' }}</div>
                        <div class="form-hint">Порядок: {{ $photo->sort_order }}</div>

                        @if($photo->tags->isNotEmpty())
                            <div class="d-flex flex-wrap gap-2 mt-3">
                                @foreach($photo->tags as $tag)
                                    <span class="chip">{{ $tag->name }}</span>
                                @endforeach
                            </div>
                        @endif

                        <div class="d-flex flex-wrap gap-2 mt-3">
                            <a class="btn-ghost" href="{{ route('admin.photos.edit', $photo) }}">Редактировать</a>
                            <form method="post" action="{{ route('admin.photos.destroy', $photo) }}" onsubmit="return confirm('Удалить фото?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-ghost">Удалить</button>
                            </form>
                        </div>
                    </article>
                </div>
            @empty
                <div class="col-12">
                    <div class="empty-state">
                        Фото пока не загружены.
                        <span>Нажмите «Добавить фото», чтобы открыть форму массовой загрузки с тегами.</span>
                    </div>
                </div>
            @endforelse
        </div>
    </section>
@endsection
