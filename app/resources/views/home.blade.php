@extends('layouts.site')

@push('structured-data')
    @include('partials.json-ld', ['data' => \App\Support\StructuredData::website()])
@endpush

@section('content')
    <section class="search-band" aria-labelledby="page-title">
        <div class="wrap">
            <div class="search-intro">
                <h1 id="page-title">Your talent. Your next move.</h1>
                <p>Find work across Africa and beyond.</p>
            </div>
            <form class="search-form" action="{{ route('jobs.index') }}" method="get" role="search">
                <div class="search-field">
                    <label for="q">What do you want to do?</label>
                    <input id="q" name="q" maxlength="100" placeholder="Job title, skill or keyword">
                </div>
                <button class="button" type="submit">
                    Find jobs
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10" cy="10" r="6"/><path d="m15 15 6 6"/></svg>
                </button>
            </form>
        </div>
    </section>

    <div class="wrap content-grid">
        <section id="jobs" class="job-section" aria-labelledby="jobs-heading">
            <div class="section-heading">
                <h2 id="jobs-heading">Latest opportunities</h2>
                <span>{{ $jobs->count() }} {{ $jobs->count() === 1 ? 'role' : 'roles' }}</span>
            </div>

            @forelse ($jobs as $job)
                @include('partials.job-row', ['job' => $job])
            @empty
                <div class="empty-state">
                    <h3>No jobs published yet.</h3>
                    <p>New roles appear here as soon as they are published. Check back soon.</p>
                </div>
            @endforelse

            @if ($jobs->isNotEmpty())
                <div class="listing-footnote">
                    <a href="{{ route('jobs.index') }}">See all open roles</a>
                </div>
            @endif
        </section>

        <aside id="career" class="career-column" aria-labelledby="career-heading">
            <div class="section-heading">
                <h2 id="career-heading">The career edit</h2>
                <span>Read &amp; grow</span>
            </div>

            @if ($leadArticle)
                <article class="lead-story">
                    @if ($leadArticle->featured_image_path)
                        @include('partials.article-art', ['article' => $leadArticle])
                    @else
                        <div class="story-art" aria-hidden="true">
                            <div class="cover-title">More than<br>a job title.<span>Your story.</span></div>
                        </div>
                    @endif
                    <p class="story-category">{{ $leadArticle->categories->first()?->name ?? 'Career advice' }}</p>
                    <h3><a href="{{ route('blog.show', $leadArticle->slug) }}">{{ $leadArticle->title }}</a></h3>
                    @if ($leadArticle->excerpt)
                        <p>{{ $leadArticle->excerpt }}</p>
                    @endif
                    <p class="story-link"><a href="{{ route('blog.show', $leadArticle->slug) }}">Read the guide</a></p>
                </article>

                @foreach ($moreArticles as $article)
                    <article class="small-story">
                        <p class="story-category">{{ $article->categories->first()?->name ?? 'Career advice' }}</p>
                        <h3><a href="{{ route('blog.show', $article->slug) }}">{{ $article->title }}</a></h3>
                        @if ($article->excerpt)
                            <p>{{ $article->excerpt }}</p>
                        @endif
                    </article>
                @endforeach

                <p class="listing-footnote"><a href="{{ route('blog.index') }}">All career advice</a></p>
            @else
                <div class="empty-state">
                    <h3>No guides published yet.</h3>
                    <p>Practical career advice appears here once an article is published.</p>
                </div>
            @endif
        </aside>
    </div>

    <section class="about-band" id="about">
        <div class="wrap about-grid">
            <h2>African talent.<br>Global possibility.</h2>
            <div>
                <p>Plusthree brings job discovery and practical career advice together, so your next step starts with the information you need.</p>
                <p class="tagline">WHERE AFRICA’S GEMS MEET GLOBAL WORK.</p>
            </div>
        </div>
    </section>

    <section class="wrap publishing" id="publishing">
        <h2>Have an opportunity to share?</h2>
        <p>Publishing is managed by the plusthree team. Send the role details to <a href="mailto:info@plusthree.io">info@plusthree.io</a> and we will review it.</p>
    </section>
@endsection
