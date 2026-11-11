@php
    $initial = mb_strtoupper(mb_substr($job->company->name, 0, 1));
    $location = collect([$job->location_city, $job->location_country])->filter()->implode(', ');
@endphp
<article class="job-row">
    <div class="job-symbol green" aria-hidden="true">{{ $initial }}</div>
    <div class="job-content">
        <p class="job-sector">
            {{ $job->company->name }}
            <span>{{ $job->employment_type->label() }}</span>
        </p>
        <h3><a href="{{ route('jobs.show', $job->slug) }}">{{ $job->title }}</a></h3>
        <div class="job-meta">
            @if ($closingLabel = $job->closingSoonLabel())
                <span class="job-flag">{{ $closingLabel }}</span>
            @endif
            @if ($location !== '')
                <span>{{ $location }}</span>
            @endif
            <span>{{ $job->work_mode->label() }}</span>
            @if ($job->closing_at && ! $closingLabel)
                <span>Closes {{ $job->closing_at->timezone('Africa/Nairobi')->format('j M Y') }}</span>
            @endif
        </div>
    </div>
</article>
