<?php

namespace App\Services;

use App\Models\Notification;
use Illuminate\Support\Str;

class NotificationService
{
    public static function create(string $orgId, string $membershipId, string $title, string $message): Notification
    {
        return Notification::create([
            'id' => (string) Str::uuid(),
            'organization_id' => $orgId,
            'membership_id' => $membershipId,
            'title' => $title,
            'message' => $message,
            'is_read' => false,
        ]);
    }
}
