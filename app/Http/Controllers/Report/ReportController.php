<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Report;
use App\Support\OrganizationContext;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    public function __construct(private OrganizationContext $ctx) {}

    public function store(Request $request)
    {
        if (!$this->ctx->hasPermission('report.generate')) return response()->json(['message'=>'auth.permission_denied'],403);
        $request->validate(['type'=>'required|string','period'=>'required|string']);
        $report = Report::create([
            'id'=>Str::uuid()->toString(),
            'organization_id'=>$this->ctx->currentOrganizationId(),
            'type'=>$request->type,
            'period'=>$request->period,
            'generated_at'=>now(),
        ]);
        return response()->json($report,201);
    }

    public function auditLogs()
    {
        if (!$this->ctx->hasPermission('audit.read')) return response()->json(['message'=>'auth.permission_denied'],403);
        return response()->json(AuditLog::where('organization_id',$this->ctx->currentOrganizationId())->orderBy('timestamp','desc')->get());
    }
}
