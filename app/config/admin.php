<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Panel allowlist
    |--------------------------------------------------------------------------
    |
    | Signing in with Google is not enough to reach /mdosi. Only addresses on
    | this list are accepted, and the first sign-in for an address creates an
    | admin account. An empty list means nobody can sign in with Google, which
    | is the correct default: the password login remains available.
    |
    | Set ADMIN_EMAILS as a comma-separated list, for example:
    | ADMIN_EMAILS=owner@example.com,editor@example.com
    |
    */

    'allowed_emails' => array_values(array_filter(array_map(
        fn (string $email): string => strtolower(trim($email)),
        explode(',', (string) env('ADMIN_EMAILS', '')),
    ))),

];
