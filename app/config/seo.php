<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Indexing
    |--------------------------------------------------------------------------
    |
    | Indexing is opt-in. A preview deployment and a production deployment can
    | share a Dockerfile, so guessing from APP_ENV would risk exposing a
    | staging copy to search engines. Set SEO_INDEXABLE=true deliberately once
    | real content is published, and never while APP_DEBUG is on.
    |
    */

    'indexable' => filter_var(env('SEO_INDEXABLE', false), FILTER_VALIDATE_BOOLEAN)
        && ! filter_var(env('APP_DEBUG', false), FILTER_VALIDATE_BOOLEAN),

    'site_name' => 'Plusthree',

    'tagline' => 'Where Africa’s gems meet global work',

    'default_description' => 'Explore opportunities across Africa and beyond, with practical advice for your next career move.',

    'logo' => '/images/plusthree-logo.png',

    /*
    |--------------------------------------------------------------------------
    | Search pages
    |--------------------------------------------------------------------------
    |
    | Filter combinations are near-duplicate pages. They stay noindex even
    | when the rest of the site is indexable, while their canonical URL
    | points at the unfiltered listing.
    |
    */

    'filter_parameters' => ['q', 'type'],

];
