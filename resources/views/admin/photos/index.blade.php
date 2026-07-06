@extends('layouts.admin')

@section('title', $selectedGallery ? 'Фото: ' . $selectedGallery->display_name : 'Фото')

@section('content')
    <section class="admin-card">
        @if(session('status'))
            <div class="alert alert-success mb-3">{{ session('status') }}</div>
        @endif

        <div class="panel__title">
            <div>
                <p class="eyebrow">Медиа</p>
                <h2>{{ $selectedGallery ? $selectedGallery->display_name : 'Фото по категориям' }}</h2>
                <p class="form-hint">
                    @if($selectedGallery)
                        Фото в категории: {{ $selectedGallery->photos_count ?? $selectedGallery->photos->count() }}
                        @if($selectedGallery->parent)
                            · Родительский каталог: {{ $selectedGallery->parent->display_name }}
                        @endif
                    @else
                        Всего фото: {{ $photosCount ?? 0 }}
                    @endif
                </p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @if($selectedGallery)
                    <a href="{{ route('admin.photos.index') }}" class="btn-ghost">
                        <x-admin-icon name="folder" />
                        <span>Все категории</span>
                    </a>
                @endif
                <a href="{{ route('admin.photos.create') }}" class="btn-soft">
                    <x-admin-icon name="plus" />
                    <span>Добавить фото</span>
                </a>
            </div>
        </div>

        @if($selectedGallery)
            @if($selectedGallery->photos->isNotEmpty())
                <div class="photo-category__grid photo-category__grid--inside">
                    @foreach($selectedGallery->photos as $photo)
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
                                    <input type="hidden" name="redirect_gallery" value="{{ $selectedGallery->id }}">
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
                    <span>Загрузите фотографии в выбранный каталог или проверьте синхронизацию FTP-папок.</span>
                </div>
            @endif
        @else
            <div class="photo-category-grid">
                @forelse(($galleries ?? collect()) as $gallery)
                    <article class="folder-card photo-category-card">
                        <a class="folder-card__link photo-category-card__link" href="{{ route('admin.photos.index', ['gallery' => $gallery->id]) }}">
                            <div class="folder-card__cover photo-category-card__cover">
                                @if($gallery->cover_image)
                                    <img src="{{ $gallery->cover_image }}" alt="{{ $gallery->display_name }}">
                                @else
                                    <x-admin-icon name="folder" />
                                @endif
                            </div>

                            <div class="folder-card__body photo-category-card__body">
                                <div>
                                    <strong>{{ $gallery->display_name }}</strong>
                                    @if($gallery->parent)
                                        <p>{{ $gallery->parent->display_name }}</p>
                                    @else
                                        <p>Корневой каталог</p>
                                    @endif
                                </div>
                                <span class="folder-card__count">{{ $gallery->photos_count }} фото</span>
                            </div>
                        </a>
                    </article>
                @empty
                    <div class="empty-state">
                        Каталогов пока нет.
                        <span>Сначала создайте каталог, затем загрузите в него фотографии.</span>
                    </div>
                @endforelse
            </div>
        @endif
    </section>
@endsection
