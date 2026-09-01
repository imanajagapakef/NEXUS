<?php

use App\Support\OrganizationContext;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withSingletons([
        OrganizationContext::class => fn () => new OrganizationContext(),
    ])
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(\App\Http\Middleware\EnsureOrganizationContext::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
