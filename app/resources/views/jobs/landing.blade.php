@extends('layouts.site')

@section('title', $heading.' — Plusthree')
@section('meta-description', $intro)

@unless ($indexable)
    {{-- Too few open roles to be worth indexing; the page still helps visitors. --}}
    @section('robots', 'noindex, follow')
@endunless

@section('content')
    <section class="search-band" aria-labelledby="jobs-title">
        <div class="wrap">
            <div class="search-intro">
                <h1 id="jobs-title">{{ $heading }}</h1>
                <p>{{ $intro }}</p>
            </div>
            <p class="landing-note">
                Looking for something else? <a href="{{ route('jobs.index') }}">See every open opportunity</a>.
            </p>
        </div>
    </section>

    <div class="wrap listing-page">
        <p class="breadcrumb">
            <a href="{{ route('jobs.index') }}">Jobs</a>
            <span aria-hidden="true">/</span>
            <span>{{ $heading }}</span>
        </p>

        @forelse ($jobs as $job)
            @include('partials.job-row', ['job' => $job])
        @empty
            <div class="empty-state">
                <h2>Nothing open here yet.</h2>
                <p>Roles appear on this page as soon as they are published.</p>
                <p><a href="{{ route('jobs.index') }}">See every open role</a></p>
            </div>
        @endforelse

        @include('partials.pagination', ['paginator' => $jobs])

        @include('partials.page-links', [
            'label' => $relatedLabel,
            'pages' => $related,
            'routeName' => $relatedRoute,
        ])
    </div>
@endsection
