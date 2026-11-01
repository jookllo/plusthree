@extends('layouts.site')

@section('title', $job->title.' — Plusthree')
@section('meta-description', Str::limit(strip_tags($job->description ?? ''), 155))
@section('og-type', 'article')
@section('canonical', route('jobs.show', $job->slug))

@push('structured-data')
    @include('partials.json-ld', ['data' => \App\Support\StructuredData::jobPosting($job)])
@endpush

@section('content')
    @php
        $location = collect([$job->location_city, $job->location_country])->filter()->implode(', ');
        $applyUrl = $job->safeApplicationUrl();
    @endphp

    <!--
        Published by Plusthree — {{ route('jobs.show', $job->slug) }}
    -->
    @include('partials.job-source-comment', ['canonicalJob' => route('jobs.show', $job->slug)])

    <div class="wrap detail-page">
        <p class="breadcrumb">
            <a href="{{ route('jobs.index') }}">Open roles</a>
            <span aria-hidden="true">/</span>
            <span>{{ $job->company->name }}</span>
        </p>

        <header class="detail-header">
            <p class="detail-kicker">{{ $job->company->name }} <span>{{ $job->employment_type->label() }}</span></p>
            <h1>{{ $job->title }}</h1>
            <div class="job-meta">
                @if ($location !== '')
                    <span>{{ $location }}</span>
                @endif
                <span>{{ $job->work_mode->label() }}</span>
                @if ($job->closing_at)
                    <span>Closes {{ $job->closing_at->timezone('Africa/Nairobi')->format('j M Y') }}</span>
                @endif
            </div>
        </header>

        @unless ($job->isOpen())
            <p class="closed-notice" role="status">
                This role closed on
                {{ $job->closing_at?->timezone('Africa/Nairobi')->format('j M Y') ?? 'an earlier date' }}
                and is no longer accepting applications.
            </p>
        @endunless

        <div class="detail-grid">
            <div class="detail-body">
                <section aria-labelledby="role-heading">
                    <h2 id="role-heading">The role</h2>
                    <div class="prose">
                        {!! nl2br(e($job->description)) !!}
                    </div>
                </section>

                @if (filled($job->requirements))
                    <section aria-labelledby="requirements-heading">
                        <h2 id="requirements-heading">What you’ll bring</h2>
                        <div class="prose">
                            {!! nl2br(e($job->requirements)) !!}
                        </div>
                    </section>
                @endif
            </div>

            <aside class="detail-aside" aria-labelledby="apply-heading">
                <h2 id="apply-heading">Apply</h2>

                @if ($job->canApply())
                    @if ($applyUrl)
                        <p>The employer collects applications on their own site.</p>
                        <a class="button detail-apply" href="{{ $applyUrl }}" rel="nofollow noopener" target="_blank">
                            Apply on employer site
                        </a>
                    @elseif (filled($job->application_email))
                        <p>Send your application directly to the employer.</p>
                        <a class="button detail-apply" href="mailto:{{ $job->application_email }}">
                            Email your application
                        </a>
                    @endif
                @elseif (! $job->isOpen())
                    <p>Applications are closed for this role.</p>
                @else
                    <p>Application details were not provided for this role.</p>
                @endif

                <dl class="detail-facts">
                    <div>
                        <dt>Contract</dt>
                        <dd>{{ $job->employment_type->label() }}</dd>
                    </div>
                    <div>
                        <dt>Work mode</dt>
                        <dd>{{ $job->work_mode->label() }}</dd>
                    </div>
                    @if ($location !== '')
                        <div>
                            <dt>Location</dt>
                            <dd>{{ $location }}</dd>
                        </div>
                    @endif
                    @if ($job->closing_at)
                        <div>
                            <dt>Closing date</dt>
                            <dd>{{ $job->closing_at->timezone('Africa/Nairobi')->format('j M Y') }}</dd>
                        </div>
                    @endif
                </dl>

                @if ($job->categories->isNotEmpty() || $job->tags->isNotEmpty())
                    <ul class="tag-list" aria-label="Sectors and tags">
                        @foreach ($job->categories as $category)
                            <li>{{ $category->name }}</li>
                        @endforeach
                        @foreach ($job->tags as $tag)
                            <li>{{ $tag->name }}</li>
                        @endforeach
                    </ul>
                @endif
            </aside>
        </div>
    </div>
@endsection
