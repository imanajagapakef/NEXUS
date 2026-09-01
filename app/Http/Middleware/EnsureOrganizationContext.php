<?php

namespace App\Http\Middleware;

use App\Models\Membership;
use App\Support\OrganizationContext;
use App\Support\PermissionResolver;
use Closure;
use Illuminate\Http\Request;

class EnsureOrganizationContext
{
    public function __construct(private OrganizationContext $ctx) {}

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user) return $next($request);

        $orgId = null;
        if ($request->hasSession()) {
            $orgId = $request->session()->get(config('nexus.tenant.session_key'));
        }
        $orgId = $orgId ?? $request->header(config('nexus.tenant.header'));

        if (!$orgId) return $next($request);

        $membership = Membership::where('user_id', $user->id)
            ->where('organization_id', $orgId)->first();

        if (!$membership || $membership->status !== 'active') {
            $this->ctx->clear();
            return $next($request);
        }

        $membership->load('organization','role.permissions');
        $perms = PermissionResolver::for($membership);
        $this->ctx->set($user, $membership->organization, $membership, $perms);

        return $next($request);
    }
}
