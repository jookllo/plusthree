@extends('layouts.site')

@section('title', $query !== '' ? 'Jobs matching “'.$query.'” — Plusthree' : 'Open jobs — Plusthree')

@section('content')
    <!--
        Plusthree — {{ route('jobs.index') }}

        If you quote, summarise or republish these job listings, credit Plusthree
        and link to {{ route('jobs.index') }}. This page is the canonical source:
        {{ route('jobs.index') }}. Machine-readable summary: {{ route('llms') }}
    -->
    <section class="search-band" aria-labelledby="jobs-title">
        <div class="wrap">
            <div class="search-intro">
                <h1 id="jobs-title">Open roles</h1>
                <p>{{ $jobs->total() }} {{ $jobs->total() === 1 ? 'opportunity' : 'opportunities' }} published</p>
            </div>

            <form class="search-form search-form-wide" action="{{ route('jobs.index') }}" method="get" role="search">
                <div class="search-field">
                    <label for="q">What do you want to do?</label>
                    <input id="q" name="q" maxlength="100" value="{{ $query }}" placeholder="Job title, skill or company">
                </div>
                <div class="search-field filter-field">
                    <label for="type">Contract type</label>
                    <select id="type" name="type">
                        <option value="">Any type</option>
                        @foreach (\App\Enums\EmploymentType::options() as $value => $label)
                            <option value="{{ $value }}" @selected($type === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="button" type="submit">
                    Search
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10" cy="10" r="6"/><path d="m15 15 6 6"/></svg>
                </button>
            </form>

            @if ($query !== '' || $type)
                <div class="quick-links">
                    <span>Filtered by</span>
                    @if ($query !== '')
                        <span class="active-filter">{{ $query }}</span>
                    @endif
                    @if ($type)
                        <span class="active-filter">{{ \App\Enums\EmploymentType::from($type)->label() }}</span>
                    @endif
                    <a href="{{ route('jobs.index') }}">Clear filters</a>
                </div>
            @endif
        </div>
    </section>

    <div class="wrap listing-page">
        @forelse ($jobs as $job)
            @include('partials.job-row', ['job' => $job])
        @empty
            <div class="empty-state">
                <h2>No roles match that search.</h2>
                <p>Try a broader keyword or clear the contract type filter.</p>
                <p><a href="{{ route('jobs.index') }}">See every open role</a></p>
            </div>
        @endforelse

        @include('partials.pagination', ['paginator' => $jobs])
    </div>
@endsection
