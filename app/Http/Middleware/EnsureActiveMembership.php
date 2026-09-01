<?php

namespace App\Http\Middleware;

use App\Support\OrganizationContext;
use Closure;
use Illuminate\Http\Request;

class EnsureActiveMembership
{
    public function __construct(private OrganizationContext $ctx) {}
    public function handle(Request $request, Closure $next)
    {
        if ($request->user() && $request->is('api/*') === false) {
            // Only enforce when org context is expected
        }
        return $next($request);
    }
}
