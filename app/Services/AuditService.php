<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Support\OrganizationContext;
use Illuminate\Support\Str;

class AuditService
{
    public static function record(string $action, string $entityType, string $entityId, OrganizationContext $ctx): AuditLog
    {
        return AuditLog::create([
            'id' => (string) Str::uuid(),
            'organization_id' => $ctx->currentOrganizationId(),
            'membership_id' => $ctx->currentMembershipId(),
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'timestamp' => now(),
        ]);
    }
}
