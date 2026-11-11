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

    /*
    |--------------------------------------------------------------------------
    | Contact
    |--------------------------------------------------------------------------
    |
    | The address shown in the footer and published as the Organization's
    | contactPoint. Keep it a single value so the visible page and the
    | structured data cannot drift apart.
    |
    */

    'contact_email' => env('SEO_CONTACT_EMAIL', 'info@plusthree.io'),

    /*
    | Profiles that describe the same organization, as a comma-separated list.
    | Empty by default: do not add a profile until the owner has one.
    */
    'same_as' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('SEO_SAME_AS', '')),
    ))),

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

    'filter_parameters' => ['q', 'type', 'closing'],

];
