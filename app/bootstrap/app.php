<?php

use App\Http\Middleware\PreventIndexing;
use App\Http\Middleware\ProtectStaging;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Dokploy terminates TLS at its proxy (Traefik). Trusting the forwarded
        // headers keeps generated URLs on https:// instead of http://.
        $middleware->trustProxies(at: '*');

        // No indexable copy should rely on the meta tag alone.
        $middleware->append(PreventIndexing::class);

        // The Jaribu preview adds a shared password on top of its admin login.
        $middleware->web(append: [ProtectStaging::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
