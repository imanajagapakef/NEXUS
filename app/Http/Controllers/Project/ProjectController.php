<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Support\OrganizationContext;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    public function __construct(private OrganizationContext $ctx) {}

    public function store(Request $request)
    {
        if (!$this->ctx->hasPermission('project.create')) return response()->json(['message'=>'auth.permission_denied'],403);
        $request->validate(['name'=>'required|string|max:100','status'=>'required|in:active,completed,archived']);
        $project = Project::create([
            'id'=>Str::uuid()->toString(),
            'organization_id'=>$this->ctx->currentOrganizationId(),
            'name'=>$request->name,
            'description'=>$request->description,
            'status'=>$request->status,
        ]);
        return response()->json($project,201);
    }

    public function index()
    {
        return response()->json(Project::where('organization_id',$this->ctx->currentOrganizationId())->get());
    }
}
