<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Belt and braces for every copy that is not meant to be indexed. The meta
 * robots tag is already set by the SEO partial; the header also covers sitemap
 * XML, plain-text routes and anything else that never renders a layout.
 */
class PreventIndexing
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! config('seo.indexable')) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive');
        }

        return $response;
    }
}
