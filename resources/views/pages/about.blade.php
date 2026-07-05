@extends('layouts.app')

@section('title', ($page->title ?? __('Обо мне')) . ' — ' . $siteName)
@section('meta_description', $page->title ?? __('Обо мне'))

@section('content')
    <article class="page-grid">
        <section class="page-hero">
            <div class="page-hero__figure">
                @if(!empty($page->image))
                    <img src="{{ $page->image }}" alt="{{ $page->title }}">
                @endif
            </div>

            <div class="page-hero__body">
                <p class="eyebrow">{{ __('Информационная страница') }}</p>
                <h1>{{ $page->title ?? __('Обо мне') }}</h1>
                <div class="page-copy">
                    {!! $page->content !!}
                </div>
            </div>
        </section>
    </article>
@endsection
