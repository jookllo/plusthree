<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.seo-meta')
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
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
        <details class="nav-menu">
            <summary @class(['active' => request()->routeIs('jobs.*')])>Find a job</summary>
            <ul class="nav-menu-panel">
                <li>
                    <a href="{{ route('jobs.index') }}"
                       @class(['active' => request()->routeIs('jobs.index') && ! in_array(request()->query('type'), [\App\Enums\EmploymentType::Fellowship->value, \App\Enums\EmploymentType::Other->value], true)])>Careers</a>
                </li>
                <li>
                    <a href="{{ route('jobs.fellowships') }}"
                       @class(['active' => request()->routeIs('jobs.fellowships') || request()->query('type') === \App\Enums\EmploymentType::Fellowship->value])>Fellowships</a>
                </li>
                <li>
                    <a href="{{ route('jobs.other') }}"
                       @class(['active' => request()->routeIs('jobs.other') || request()->query('type') === \App\Enums\EmploymentType::Other->value])>Other opportunities</a>
                </li>
            </ul>
        </details>
        <a href="{{ route('blog.index') }}" @class(['active' => request()->routeIs('blog.*')])>Career advice</a>
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
