<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;

class TenantGuard
{
    public static function ensureSameOrg(Model $entity, string $orgId, string $field = 'organization_id'): void
    {
        if (($entity->$field ?? null) !== $orgId) {
            abort(404, 'tenant.not_found');
        }
    }
}
