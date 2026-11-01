@extends('layouts.site')

@section('title', $article->metaTitle().' — Plusthree')
@section('meta-description', $article->metaDescription())
@section('og-type', 'article')
@section('canonical', route('blog.show', $article->slug))
@if ($article->featured_image_path)
    @section('og-image', url(Storage::disk('public')->url($article->featured_image_path)))
@endif

@push('structured-data')
    @include('partials.json-ld', ['data' => \App\Support\StructuredData::article($article)])
@endpush

@section('content')
    <div class="wrap article-page">
        <p class="breadcrumb">
            <a href="{{ route('blog.index') }}">Career advice</a>
            @if ($article->categories->isNotEmpty())
                <span aria-hidden="true">/</span>
                <a href="{{ route('blog.category', $article->categories->first()->slug) }}">
                    {{ $article->categories->first()->name }}
                </a>
            @endif
        </p>

        <header class="article-hero">
            <div class="article-hero-media">
                @include('partials.article-art', ['article' => $article])
            </div>
            <div class="article-hero-body">
                @if ($article->categories->isNotEmpty())
                    <p class="story-category">{{ $article->categories->first()->name }}</p>
                @endif
                <h1>{{ $article->title }}</h1>
                @if ($article->excerpt)
                    <p class="article-standfirst">{{ $article->excerpt }}</p>
                @endif
                <p class="article-meta">
                    <span>{{ $article->published_at?->timezone('Africa/Nairobi')->format('j F Y') }}</span>
                    @if ($article->author)
                        <span>{{ $article->author->name }}</span>
                    @endif
                    <span>{{ $article->readingTimeMinutes() }} min read</span>
                </p>
            </div>
        </header>

        @if ($preview ?? false)
            <p class="preview-banner" role="status">
                Preview — this article is not published. Only people with this link can see it.
            </p>
        @endif

        <div class="article-body prose">
            {!! $article->renderedBody() !!}
        </div>

        @if ($article->tags->isNotEmpty())
            <ul class="tag-list" aria-label="Tags">
                @foreach ($article->tags as $tag)
                    <li>{{ $tag->name }}</li>
                @endforeach
            </ul>
        @endif

        @if ($related->isNotEmpty())
            <section class="related" aria-labelledby="related-heading">
                <div class="section-heading">
                    <h2 id="related-heading">Keep reading</h2>
                    <a href="{{ route('blog.index') }}">All career advice</a>
                </div>
                <div class="article-grid">
                    @foreach ($related as $item)
                        @include('partials.article-card', ['article' => $item])
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection
