<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.seo-meta')
    {{-- The ?v= busts a Cloudflare-cached zero-byte /favicon.ico from before
         the icon existed. Bump it if the icon is regenerated. --}}
    <link rel="icon" href="{{ asset('favicon.ico') }}?v=2" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <meta name="theme-color" content="#23305C">
    @stack('structured-data')
    {{-- Sitewide publisher identity, after any page-specific block so the page's
         own schema is the first thing a reader of the head meets. --}}
    @include('partials.json-ld', ['data' => \App\Support\StructuredData::organization()])
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<a class="skip-link" href="#main">Skip to content</a>
<div class="brand-line" aria-hidden="true"></div>
<header class="masthead wrap">
    <a class="brand" href="{{ route('home') }}" aria-label="Plusthree home">
        <img src="{{ asset('images/plusthree-logo.png') }}" width="280" height="112" alt="Plusthree — WHERE AFRICA’S GEMS MEET GLOBAL WORK.">
    </a>
    <nav aria-label="Main navigation">
        @php
            $filters = request()->query();
            $onJobs = request()->routeIs('jobs.index')
                && ! in_array($filters['type'] ?? null, [\App\Enums\EmploymentType::Fellowship->value, \App\Enums\EmploymentType::Other->value], true);
            $onFellowships = request()->routeIs('jobs.fellowships')
                || ($filters['type'] ?? null) === \App\Enums\EmploymentType::Fellowship->value;
            $onOther = request()->routeIs('jobs.other')
                || ($filters['type'] ?? null) === \App\Enums\EmploymentType::Other->value;
            $onBlog = request()->routeIs('blog.*');
        @endphp
        <a href="{{ route('jobs.index') }}" @class(['active' => $onJobs]) @if ($onJobs) aria-current="page" @endif>Jobs</a>
        <a href="{{ route('jobs.fellowships') }}" @class(['active' => $onFellowships]) @if ($onFellowships) aria-current="page" @endif>Fellowships</a>
        <a href="{{ route('jobs.other') }}" @class(['active' => $onOther]) @if ($onOther) aria-current="page" @endif>Other opportunities</a>
        <a href="{{ route('blog.index') }}" @class(['active' => $onBlog]) @if ($onBlog) aria-current="page" @endif>Career advice</a>
        <a href="{{ route('home') }}#about">About plusthree</a>
    </nav>
    <a class="nav-note" href="{{ route('employers.show') }}">For employers</a>
</header>
<main id="main">
    @yield('content')
</main>
<footer class="wrap footer">
    <div class="footer-left">
        <a href="{{ route('home') }}" class="footer-brand">plusthree</a>
        <p>© {{ date('Y') }} Plusthree</p>
    </div>

    {{-- The owner asked for the address as an icon rather than a sentence; the
         email itself stays in the accessible name and the href. --}}
    <a class="footer-mail" href="mailto:{{ config('seo.contact_email') }}">
        <span class="visually-hidden">Email {{ config('seo.contact_email') }}</span>
        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor"
             stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
             aria-hidden="true" focusable="false">
            <rect x="3" y="5" width="18" height="14" rx="2"/>
            <path d="m3.7 6.3 8.3 6.2 8.3-6.2"/>
        </svg>
    </a>

    <a class="footer-back" href="#main">Back to top</a>
</footer>
</body>
</html>
