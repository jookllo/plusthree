<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Analytics and session recorder
    |--------------------------------------------------------------------------
    |
    | Cheki is self-hosted at cheki.plusthree.io and reports to the owner's
    | dashboard. Both tags are emitted from the public layout only; the /mdosi
    | panel uses Filament's own layout, so editorial activity is not recorded.
    |
    | On by default for real visitors (production) and off in local, testing and
    | staging, so development browsing, the test suite and preview copies do not
    | land in the owner's report. Set ANALYTICS_ENABLED=false to silence any
    | other copy that shares the same website id.
    |
    */

    'enabled' => filter_var(
        env('ANALYTICS_ENABLED', ! in_array(env('APP_ENV', 'production'), ['local', 'testing', 'staging'], true)),
        FILTER_VALIDATE_BOOLEAN,
    ),

    'host' => rtrim((string) env('ANALYTICS_HOST', 'https://cheki.plusthree.io'), '/'),

    'website_id' => (string) env('ANALYTICS_WEBSITE_ID', '517fdb7f-a001-43a8-989f-75a6823dea18'),

];
