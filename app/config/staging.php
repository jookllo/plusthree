<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Staging preview (Jaribu)
    |--------------------------------------------------------------------------
    |
    | A single shared password guards the preview copy when APP_ENV=staging.
    | Leave both values empty to disable the prompt; production never reads
    | these because the environment check fails first.
    |
    */

    'basic_auth' => [
        'username' => env('STAGING_BASIC_AUTH_USER'),
        'password' => env('STAGING_BASIC_AUTH_PASSWORD'),
    ],

];
