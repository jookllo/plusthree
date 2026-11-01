@extends('layouts.site')

@section('title', $category->name.' — Plusthree career advice')
@section('meta-description', 'Articles filed under '.$category->name.' on Plusthree.')

@section('content')
    <section class="page-band">
        <div class="wrap">
            <p class="breadcrumb">
                <a href="{{ route('blog.index') }}">Career advice</a>
                <span aria-hidden="true">/</span>
                <span>{{ $category->name }}</span>
            </p>
            <h1>{{ $category->name }}</h1>
            <p class="band-lede">Every guide filed under this topic, newest first.</p>
        </div>
    </section>

    <div class="wrap listing-page">
        @if ($articles->isEmpty())
            <div class="empty-state">
                <h2>Nothing filed here yet.</h2>
                <p>No published articles use this category at the moment.</p>
                <p><a href="{{ route('blog.index') }}">Read all career advice</a></p>
            </div>
        @else
            <div class="article-grid">
                @foreach ($articles as $article)
                    @include('partials.article-card', ['article' => $article])
                @endforeach
            </div>
        @endif

        @include('partials.pagination', ['paginator' => $articles])
    </div>
@endsection
