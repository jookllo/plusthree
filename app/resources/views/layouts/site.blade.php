<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.seo-meta')
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
                       @class(['active' => request()->routeIs('jobs.*') && ! request()->query('type')])>Careers</a>
                </li>
                <li>
                    <a href="{{ route('jobs.index', ['type' => \App\Enums\EmploymentType::Fellowship->value]) }}"
                       @class(['active' => request()->query('type') === \App\Enums\EmploymentType::Fellowship->value])>Fellowships</a>
                </li>
                <li>
                    <a href="{{ route('jobs.index', ['type' => \App\Enums\EmploymentType::Other->value]) }}"
                       @class(['active' => request()->query('type') === \App\Enums\EmploymentType::Other->value])>Other opportunities</a>
                </li>
            </ul>
        </details>
        <a href="{{ route('blog.index') }}" @class(['active' => request()->routeIs('blog.*')])>Career advice</a>
        <a href="{{ route('home') }}#about">About plusthree</a>
    </nav>
    <a class="nav-note" href="mailto:info@plusthree.io">For employers</a>
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
