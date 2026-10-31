<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Plusthree — Jobs & career advice for African talent</title>
    <meta name="description" content="Explore opportunities across Africa and beyond, with practical advice for your next career move.">
    <meta name="robots" content="noindex, nofollow">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<a class="skip-link" href="#main">Skip to content</a>
<div class="brand-line" aria-hidden="true"></div>
<header class="masthead wrap">
    <a class="brand" href="{{ route('home') }}" aria-label="Plusthree home"><img src="{{ asset('images/plusthree-logo.png') }}" width="280" height="112" alt="Plusthree — WHERE AFRICA’S GEMS MEET GLOBAL WORK."></a>
    <nav aria-label="Main navigation"><a href="#jobs" class="active">Find a job</a><a href="#career">Career advice</a><a href="#about">About plusthree</a></nav>
    <a class="nav-note" href="#publishing">For employers</a>
</header>
<main id="main">
    <section class="search-band" aria-labelledby="page-title">
        <div class="wrap">
            <div class="search-intro"><h1 id="page-title">Your talent. Your next move.</h1><p>Find work across Africa and beyond.</p></div>
            <form class="search-form" action="{{ route('home') }}#jobs" method="get" role="search">
                <div class="search-field"><label for="q">What do you want to do?</label><input id="q" name="q" maxlength="100" value="{{ $query }}" placeholder="Job title, skill or keyword"></div>
                <button class="button" type="submit">Find jobs <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10" cy="10" r="6"/><path d="m15 15 6 6"/></svg></button>
            </form>
            <div class="quick-links"><span>Explore</span><a href="?q=remote#jobs">Remote work</a><a href="?q=intern#jobs">Internships</a><a href="?q=design#jobs">Design & creative</a><a href="?q=programme#jobs">Social impact</a></div>
        </div>
    </section>
    <div class="wrap notice"><span>Design preview</span><p>Try the search and filters below. These are sample roles, not live vacancies.</p></div>
    <div class="wrap content-grid">
        <section id="jobs" class="job-section" aria-labelledby="jobs-heading">
            <div class="section-heading"><h2 id="jobs-heading">Find your next opportunity</h2><span>{{ count($jobs) }} sample {{ count($jobs) === 1 ? 'role' : 'roles' }}</span></div>
            <div class="job-tabs" aria-label="Filter sample jobs"><a class="{{ !$type ? 'selected' : '' }}" href="{{ route('home', ['q' => $query]) }}#jobs" @if(!$type) aria-current="true" @endif>All opportunities</a>@foreach(['Full-time', 'Contract', 'Internship'] as $jobType)<a class="{{ $type === $jobType ? 'selected' : '' }}" href="{{ route('home', ['q' => $query, 'type' => $jobType]) }}#jobs" @if($type === $jobType) aria-current="true" @endif>{{ $jobType }}</a>@endforeach</div>
            @forelse($jobs as $job)
            <article class="job-row">
                <div class="job-symbol {{ $job['tone'] }}" aria-hidden="true">{{ $job['symbol'] }}</div>
                <div class="job-content"><p class="job-sector">{{ $job['sector'] }} <span>Sample listing</span></p><h3>{{ $job['title'] }}</h3><div class="job-meta"><span>{{ $job['location'] }}</span><span>{{ $job['type'] }}</span><span>{{ $job['mode'] }}</span></div><details><summary>View role preview</summary><div class="role-preview"><p>{{ $job['description'] }}</p><p>This demonstrates a listing layout. No employer is recruiting through this example and applications are unavailable.</p></div></details></div>
            </article>
            @empty
            <div class="empty-state"><h3>No sample roles match this search.</h3><p>Try another keyword or clear the job-type filter.</p><a href="{{ route('home') }}#jobs">Clear all filters</a></div>
            @endforelse
            <div class="listing-footnote">Good opportunities deserve a closer look.<br><span>Published jobs will include employer details, requirements and application instructions.</span></div>
        </section>
        <aside id="career" aria-labelledby="career-heading" class="career-column">
            <div class="section-heading"><h2 id="career-heading">The career edit</h2><span>Read & grow</span></div>
            <article class="lead-story"><div class="story-art" aria-hidden="true"><div class="cover-title">More than<br>a job title.<span>Your story.</span></div></div><p class="story-category">CVs & applications</p><h3>A CV that tells your story.</h3><p>Make your experience easy to understand, and your strengths hard to miss.</p><details><summary>Read the quick guide</summary><div class="story-body"><p>Start with the role you want. Choose experience that shows you can do that work, rather than listing every responsibility you have held.</p><p>Use concrete examples: what you did, how you did it, and the result. Include numbers only when you can verify them.</p><p>Keep headings simple, check your contact details, and follow the employer’s requested file format.</p></div></details></article>
            <article class="small-story"><p class="story-category">Interviews</p><h3>Bring examples, not just answers.</h3><details><summary>Read interview tips</summary><p class="story-body">Prepare two or three examples of work you are proud of. Explain the situation, your contribution and what changed. Practise aloud, and prepare questions about the team and its expectations.</p></details></article>
            <article class="small-story"><p class="story-category">Remote work</p><h3>Look beyond “work from anywhere”.</h3><details><summary>Read the remote-work checklist</summary><p class="story-body">Check eligible countries, time-zone overlap, employment arrangements and equipment expectations before applying. “Remote” does not always mean available in every country.</p></details></article>
        </aside>
    </div>
    <section class="about-band" id="about"><div class="wrap about-grid"><h2>African talent.<br>Global possibility.</h2><div><p>Plusthree brings job discovery and practical career advice together, so your next step starts with the information you need.</p><p class="tagline">WHERE AFRICA’S GEMS MEET GLOBAL WORK.</p></div></div></section>
    <section class="wrap publishing" id="publishing"><h2>Have an opportunity to share?</h2><p>Job publishing will be managed by the plusthree team. Employer submissions are not open yet.</p></section>
</main>
<footer class="wrap footer"><a href="{{ route('home') }}" class="footer-brand">plusthree</a><p>© {{ date('Y') }} Plusthree</p><a href="#main">Back to top</a></footer>
</body>
</html>
