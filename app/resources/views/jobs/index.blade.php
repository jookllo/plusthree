@extends('layouts.site')

@section('title', $query !== ''
    ? $heading.' matching “'.$query.'” — Plusthree'
    : $heading.' — Plusthree')

@section('content')
    @include('partials.job-source-comment')
    <section class="search-band" aria-labelledby="jobs-title">
        <div class="wrap">
            <div class="search-intro">
                <h1 id="jobs-title">{{ $heading }}</h1>
                <p>{{ $intro ?? $jobs->total().' '.($jobs->total() === 1 ? 'opportunity' : 'opportunities').' published' }}</p>
            </div>

            <form @class(['search-form', 'search-form-wide' => ! $landing, 'search-form-landing' => $landing]) action="{{ request()->url() }}" method="get" role="search">
                <div class="search-field">
                    <label for="q">What do you want to do?</label>
                    <input id="q" name="q" maxlength="100" value="{{ $query }}" placeholder="Job title, skill or company">
                </div>
                @unless ($landing)
                    <div class="search-field filter-field">
                        <label for="type">Contract type</label>
                        <select id="type" name="type">
                            <option value="">Any type</option>
                            @foreach (\App\Enums\EmploymentType::options() as $value => $label)
                                <option value="{{ $value }}" @selected($type === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                @endunless
                <button class="button" type="submit">
                    Search
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10" cy="10" r="6"/><path d="m15 15 6 6"/></svg>
                </button>
            </form>

            @if ($query !== '' || ($type && ! $landing))
                <div class="quick-links">
                    <span>Filtered by</span>
                    @if ($query !== '')
                        <span class="active-filter">{{ $query }}</span>
                    @endif
                    @if ($type && ! $landing)
                        <span class="active-filter">{{ \App\Enums\EmploymentType::from($type)->label() }}</span>
                    @endif
                    <a href="{{ request()->url() }}">Clear filters</a>
                </div>
            @endif

            @if ($landing)
                <p class="landing-note">
                    Looking for something else? <a href="{{ route('jobs.index') }}">See every open opportunity</a>.
                </p>
            @endif
        </div>
    </section>

    <div class="wrap listing-page">
        @forelse ($jobs as $job)
            @include('partials.job-row', ['job' => $job])
        @empty
            <div class="empty-state">
                <h2>{{ $landing ? 'Nothing here yet.' : 'No roles match that search.' }}</h2>
                @if ($landing)
                    <p>{{ $heading }} appear here when they are published. Check back, or browse every open opportunity.</p>
                @else
                    <p>Try a broader keyword or clear the contract type filter.</p>
                @endif
                <p><a href="{{ route('jobs.index') }}">See every open role</a></p>
            </div>
        @endforelse

        @include('partials.pagination', ['paginator' => $jobs])
    </div>
@endsection
