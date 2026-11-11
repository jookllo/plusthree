@php
    $siteName = config('seo.site_name');
    $pageTitle = trim($__env->yieldContent('title'));
    $pageTitle = $pageTitle !== '' ? $pageTitle : $siteName.' — Jobs & career advice for African talent';
    $description = trim($__env->yieldContent('meta-description'));
    $description = $description !== '' ? $description : config('seo.default_description');

    // Filter and search URLs are duplicates of the listing they came from;
    // a signed preview of an unpublished record must not be indexed either.
    $hasFilter = collect(config('seo.filter_parameters'))
        ->contains(fn (string $parameter): bool => request()->filled($parameter));

    $indexable = config('seo.indexable') && ! $hasFilter && ! ($preview ?? false);

    $canonical = trim($__env->yieldContent('canonical'));

    if ($canonical === '') {
        $canonical = url()->current();

        // Page 2 of a listing is a different page, not a duplicate of page 1.
        // Canonicalising it to the unfiltered first page would tell Google to
        // ignore every role that only appears further down the board.
        $page = (int) request()->query('page', 1);

        if (! $hasFilter && $page > 1) {
            $canonical = url()->current().'?page='.$page;
        }
    }

    $ogImage = trim($__env->yieldContent('og-image'));
    $ogType = trim($__env->yieldContent('og-type')) ?: 'website';

    // A page can override the policy, e.g. a sector page with too few roles to
    // be worth indexing. Everything else follows the indexable switch.
    $robots = trim($__env->yieldContent('robots'));
    $robots = $robots !== '' ? $robots : ($indexable ? 'index, follow' : 'noindex, nofollow');
@endphp
<title>{{ $pageTitle }}</title>
<meta name="description" content="{{ $description }}">
<meta name="robots" content="{{ $robots }}">
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
