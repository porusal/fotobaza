@extends('layouts.app')

@section('title', ($page->title ?? 'Страница') . ' — ' . $siteName)
@section('meta_description', $page->title ?? 'Страница')

@section('content')
    <article class="page-grid">
        <section class="page-hero">
            <div class="page-hero__figure">
                @if(!empty($page->image))
                    <img src="{{ $page->image }}" alt="{{ $page->title }}">
                @endif
            </div>

            <div class="page-hero__body">
                <p class="eyebrow">Информационная страница</p>
                <h1>{{ $page->title ?? 'Страница' }}</h1>
                <div class="page-copy">
                    {!! $page->content !!}
                </div>
            </div>
        </section>
    </article>
@endsection
