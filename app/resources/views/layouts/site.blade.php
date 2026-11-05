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
            $onCareers = request()->routeIs('jobs.index')
                && ! in_array($filters['type'] ?? null, [\App\Enums\EmploymentType::Fellowship->value, \App\Enums\EmploymentType::Other->value], true);
            $onFellowships = request()->routeIs('jobs.fellowships')
                || ($filters['type'] ?? null) === \App\Enums\EmploymentType::Fellowship->value;
            $onOther = request()->routeIs('jobs.other')
                || ($filters['type'] ?? null) === \App\Enums\EmploymentType::Other->value;
            $onBlog = request()->routeIs('blog.*');
        @endphp
        <a href="{{ route('jobs.index') }}" @class(['active' => $onCareers]) @if ($onCareers) aria-current="page" @endif>Careers</a>
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
    <a href="{{ route('home') }}" class="footer-brand">plusthree</a>
    <p>© {{ date('Y') }} Plusthree</p>
    <a href="#main">Back to top</a>
</footer>
</body>
</html>
