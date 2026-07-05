@extends('layouts.app')

@section('title', $gallery->display_name . ' — ' . $siteName)
@section('meta_description', $gallery->description ?: $gallery->display_name)

@section('content')
    <div class="page-grid page-grid--sidebar">
        <aside class="page-sidebar d-none d-lg-grid">
            <div class="sidebar-panel">
                <div class="sidebar-card">
                    <div class="sidebar-card__title">{{ __('Каталоги') }}</div>
                    <x-gallery-tree :items="$galleryTree" :active-slug="$gallery->slug" :open-ids="$galleryTreeOpenIds ?? []" />
                </div>

                <div class="sidebar-card">
                    <div class="sidebar-card__title">{{ __('Теги этой категории') }}</div>
                    <x-tag-cloud :tags="$galleryTags" :base-url="url('/gallery/' . $gallery->slug)" :all-url="url('/gallery/' . $gallery->slug)" :active-tag="$activeTag ?? null" />
                </div>
            </div>
        </aside>

        <article class="page-content">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-custom">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">{{ __('Главная') }}</a></li>
                    @if($gallery->parent)
                        <li class="breadcrumb-item"><a href="{{ url('/gallery/' . $gallery->parent->slug) }}">{{ $gallery->parent->display_name }}</a></li>
                    @endif
                    <li class="breadcrumb-item active" aria-current="page">{{ $gallery->display_name }}</li>
                </ol>
            </nav>

            <section class="page-hero page-hero--split">
                <div class="page-hero__figure">
                    @if(!empty($gallery->cover_image))
                        <img src="{{ $gallery->cover_image }}" alt="{{ $gallery->display_name }}">
                    @endif
                </div>

                <div class="page-hero__body">
                    <p class="eyebrow">{{ __('Каталог') }} / {{ $gallery->name }}</p>
                    <h1>{{ $gallery->display_name }}</h1>
                    <div class="page-copy">
                        {!! $gallery->description !!}
                    </div>
                </div>
            </section>

            <section class="section">
                <div class="section-head">
                    <p class="eyebrow">{{ __('Содержимое каталога') }}</p>
                    <div class="section-head__row">
                        <h2>{{ $gallery->photos->isNotEmpty() ? __('Фото каталога') : __('Подкаталоги') }}</h2>
                        <div class="section-head__actions">
                            <span class="chip">{{ $gallery->photos->count() }} {{ __('Фото') }}</span>
                            <span class="chip">{{ $gallery->children->count() }} {{ __('Подкаталоги') }}</span>
                        </div>
                    </div>
                </div>

                @if($gallery->photos->isNotEmpty())
                    <x-photo-grid :photos="$gallery->photos" :columns="$gridColumns ?? 3" :gap="$galleryGap ?? '1rem'" :group="$gallery->slug" />
                @else
                    <div class="gallery-grid gallery-grid--3" style="--gallery-columns: {{ $gridColumns ?? 3 }}; --gallery-gap: {{ $galleryGap ?? '1rem' }};">
                        @forelse($gallery->children as $child)
                            <article class="folder-card">
                                <a class="folder-card__link" href="{{ url('/gallery/' . $child->slug) }}">
                                    <div class="folder-card__cover">
                                        @if(!empty($child->cover_image))
                                            <img src="{{ $child->cover_image }}" alt="{{ $child->display_name }}">
                                        @endif
                                    </div>
                                    <div class="folder-card__body">
                                        <div>
                                            <strong>{{ $child->display_name }}</strong>
                                            <p>{{ $child->description ? \Illuminate\Support\Str::limit(strip_tags($child->description ?? ''), 90) : __('Открыть подкаталог') }}</p>
                                        </div>
                                        <span class="folder-card__count">{{ $child->photos_count ?? 0 }} {{ __('Фото') }}</span>
                                    </div>
                                </a>
                            </article>
                        @empty
                            <div class="empty-state">{{ __('В этом разделе ещё нет фото и подкаталогов.') }}</div>
                        @endforelse
                    </div>
                @endif
            </section>
        </article>
    </div>
@endsection
