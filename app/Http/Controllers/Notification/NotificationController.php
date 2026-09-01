<?php

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Support\OrganizationContext;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(private OrganizationContext $ctx) {}

    public function index()
    {
        return response()->json(
            Notification::where('organization_id',$this->ctx->currentOrganizationId())
                ->where('membership_id',$this->ctx->currentMembershipId())->get()
        );
    }

    public function markRead(string $id)
    {
        $n = Notification::where('id',$id)->where('organization_id',$this->ctx->currentOrganizationId())->firstOrFail();
        if ($n->membership_id !== $this->ctx->currentMembershipId()) abort(404,'tenant.not_found');
        $n->update(['is_read'=>true]);
        return response()->json($n);
    }
}
