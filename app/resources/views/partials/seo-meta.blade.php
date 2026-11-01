@php
    $siteName = config('seo.site_name');
    $pageTitle = trim($__env->yieldContent('title'));
    $pageTitle = $pageTitle !== '' ? $pageTitle : $siteName.' — Jobs & career advice for African talent';
    $description = trim($__env->yieldContent('meta-description'));
    $description = $description !== '' ? $description : config('seo.default_description');

    // Filter and search URLs are duplicates of the listing they came from.
    $hasFilter = collect(config('seo.filter_parameters'))
        ->contains(fn (string $parameter): bool => request()->filled($parameter));

    $indexable = config('seo.indexable') && ! $hasFilter;

    $canonical = trim($__env->yieldContent('canonical'));
    $canonical = $canonical !== '' ? $canonical : url()->current();

    $ogImage = trim($__env->yieldContent('og-image'));
    $ogType = trim($__env->yieldContent('og-type')) ?: 'website';
@endphp
<title>{{ $pageTitle }}</title>
<meta name="description" content="{{ $description }}">
<meta name="robots" content="{{ $indexable ? 'index, follow' : 'noindex, nofollow' }}">
<link rel="canonical" href="{{ $canonical }}">

<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:type" content="{{ $ogType }}">
<meta property="og:title" content="{{ $pageTitle }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:url" content="{{ $canonical }}">
@if ($ogImage !== '')
    <meta property="og:image" content="{{ $ogImage }}">
@endif
<meta name="twitter:card" content="{{ $ogImage !== '' ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $pageTitle }}">
<meta name="twitter:description" content="{{ $description }}">
