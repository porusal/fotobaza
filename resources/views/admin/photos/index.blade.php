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
                <h2>Фото по категориям</h2>
                <p class="form-hint">Всего фото: {{ $photosCount ?? 0 }}</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('admin.photos.create') }}" class="btn-soft">
                    <x-admin-icon name="plus" />
                    <span>Добавить фото</span>
                </a>
            </div>
        </div>

        <div class="photo-category-list">
            @forelse(($galleries ?? collect()) as $gallery)
                <details class="photo-category" @if($gallery->photos_count > 0) open @endif>
                    <summary class="photo-category__summary">
                        <span class="photo-category__title">
                            <strong>{{ $gallery->display_name }}</strong>
                            @if($gallery->parent)
                                <span>{{ $gallery->parent->display_name }}</span>
                            @endif
                        </span>
                        <span class="chip">{{ $gallery->photos_count }}</span>
                    </summary>

                    @if($gallery->photos->isNotEmpty())
                        <div class="photo-category__grid">
                            @foreach($gallery->photos as $photo)
                                <article class="admin-photo-card">
                                    <div class="folder-card__cover">
                                        <img src="{{ $photo->path }}" alt="{{ $photo->alt_text ?: $photo->filename }}">
                                    </div>

                                    <div class="admin-photo-card__body">
                                        <strong>{{ $photo->filename }}</strong>
                                        <span>Порядок: {{ $photo->sort_order }}</span>

                                        @if($photo->tags->isNotEmpty())
                                            <div class="d-flex flex-wrap gap-2 mt-2">
                                                @foreach($photo->tags as $tag)
                                                    <span class="chip">{{ $tag->name }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>

                                    <div class="admin-photo-card__actions">
                                        <a class="btn-ghost icon-button" href="{{ route('admin.photos.edit', $photo) }}" title="Редактировать" aria-label="Редактировать">
                                            <x-admin-icon name="edit" />
                                        </a>
                                        <form method="post" action="{{ route('admin.photos.destroy', $photo) }}" onsubmit="return confirm('Удалить фото?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-ghost icon-button" title="Удалить" aria-label="Удалить">
                                                <x-admin-icon name="trash" />
                                            </button>
                                        </form>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state empty-state--compact">
                            В этом каталоге пока нет фото.
                        </div>
                    @endif
                </details>
            @empty
                <div class="empty-state">
                    Каталогов пока нет.
                    <span>Сначала создайте каталог, затем загрузите в него фотографии.</span>
                </div>
            @endforelse
        </div>
    </section>
@endsection
