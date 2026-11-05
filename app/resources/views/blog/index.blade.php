@extends('layouts.site')

@section('title', 'Career advice — Plusthree')
@section('meta-description', 'Practical guides on CVs, interviews, applications and building a career across Africa and beyond.')

@section('content')
    <section class="page-band">
        <div class="wrap">
            <p class="band-kicker">The Career Edit</p>
            <h1>Advice that moves you forward.</h1>
            <p class="band-lede">Clear, practical guidance on applications, interviews and the working life that follows.</p>
        </div>
    </section>

    <div class="wrap listing-page">
        @if ($categories->isNotEmpty())
            <nav class="topic-nav" aria-label="Article categories">
                <span>Browse</span>
                <a href="{{ route('blog.index') }}" class="selected">All</a>
                @foreach ($categories as $category)
                    <a href="{{ route('blog.category', $category->slug) }}">
                        {{ $category->name }}
                        @if ($category->articles_count)
                            <small>{{ $category->articles_count }}</small>
                        @endif
                    </a>
                @endforeach
            </nav>
        @endif

        @if ($articles->isEmpty())
            <div class="empty-state">
                <h2>No guides published yet.</h2>
                <p>Practical career advice appears here once an article is published.</p>
            </div>
        @else
            @php
                $lead = $articles->first();
                $rest = $articles->getCollection()->skip(1);
            @endphp

            <article class="lead-article">
                @include('partials.article-art', ['article' => $lead, 'allowFallback' => false])
                @if ($lead->categories->isNotEmpty())
                    <p class="story-category">{{ $lead->categories->first()->name }}</p>
                @endif
                <h2><a href="{{ route('blog.show', $lead->slug) }}">{{ $lead->title }}</a></h2>
                @if ($lead->excerpt)
                    <p class="lead-summary">{{ $lead->excerpt }}</p>
                @endif
                <p class="article-meta">
                    <span>{{ $lead->published_at?->timezone('Africa/Nairobi')->format('j M Y') }}</span>
                    @if ($lead->author)
                        <span>{{ $lead->author->name }}</span>
                    @endif
                    <span>{{ $lead->readingTimeMinutes() }} min read</span>
                </p>
                <p class="story-link"><a href="{{ route('blog.show', $lead->slug) }}">Read the guide</a></p>
            </article>

            @if ($rest->isNotEmpty())
                <div class="article-grid">
                    @foreach ($rest as $article)
                        @include('partials.article-card', ['article' => $article])
                    @endforeach
                </div>
            @endif
        @endif

        @include('partials.pagination', ['paginator' => $articles])
    </div>
@endsection
