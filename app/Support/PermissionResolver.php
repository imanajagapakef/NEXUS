<?php

namespace App\Support;

use App\Models\Membership;

class PermissionResolver
{
    public static function for(Membership $membership): array
    {
        $membership->loadMissing('role.permissions');
        return $membership->role?->permissions->pluck('name')->all() ?? [];
    }
}
