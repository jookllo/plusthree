<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Plusthree — Jobs & career advice for African talent')</title>
    <meta name="description" content="@yield('meta-description', 'Explore opportunities across Africa and beyond, with practical advice for your next career move.')">
    <meta name="robots" content="noindex, nofollow">
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
        <a href="{{ route('jobs.index') }}" @class(['active' => request()->routeIs('jobs.*')])>Find a job</a>
        <a href="{{ route('blog.index') }}" @class(['active' => request()->routeIs('blog.*')])>Career advice</a>
        <a href="{{ route('home') }}#about">About plusthree</a>
    </nav>
    <a class="nav-note" href="{{ route('home') }}#publishing">For employers</a>
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
