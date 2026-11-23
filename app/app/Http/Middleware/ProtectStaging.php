<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * One shared password in front of the Jaribu preview. Only active when the
 * application environment is "staging" and credentials are configured, so a
 * production deploy cannot accidentally lock visitors out.
 */
class ProtectStaging
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->environment('staging')) {
            return $next($request);
        }

        $username = (string) config('staging.basic_auth.username');
        $password = (string) config('staging.basic_auth.password');

        if ($username === '' || $password === '') {
            return $next($request);
        }

        $givenUser = (string) $request->getUser();
        $givenPassword = (string) $request->getPassword();

        if (hash_equals($username, $givenUser) && hash_equals($password, $givenPassword)) {
            return $next($request);
        }

        return response('Authentication required.', 401, [
            'WWW-Authenticate' => 'Basic realm="Plusthree Jaribu", charset="UTF-8"',
        ]);
    }
}
