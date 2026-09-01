<?php

namespace App\Http\Controllers\Expense;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Policies\ExpensePolicy;
use App\Services\Expense\ExpenseService;
use App\Support\OrganizationContext;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function __construct(private OrganizationContext $ctx, private ExpensePolicy $policy) {}

    public function store(Request $request)
    {
        if (!$this->policy->create($this->ctx)) return response()->json(['message'=>'auth.permission_denied'],403);
        $request->validate(['description'=>'required|string','amount'=>'required|numeric|min:0.01']);
        $expense = ExpenseService::create($request->only('description','amount'), $this->ctx);
        return response()->json($expense,201);
    }

    public function show(string $id)
    {
        $expense = Expense::findOrFail($id);
        if (!$this->policy->view($expense,$this->ctx)) return response()->json(['message'=>'tenant.not_found'],404);
        return response()->json($expense);
    }

    public function submit(Request $request, string $id)
    {
        $expense = Expense::findOrFail($id);
        if (!$this->policy->submit($expense,$this->ctx)) return response()->json(['message'=>'auth.permission_denied'],403);
        $request->validate(['reviewer_membership_id'=>'required|uuid']);
        $expense = ExpenseService::submit($expense,$this->ctx,$request->reviewer_membership_id);
        return response()->json($expense);
    }

    public function approve(string $id)
    {
        $expense = Expense::findOrFail($id);
        if (!$this->policy->approve($expense,$this->ctx)) return response()->json(['message'=>'auth.permission_denied'],403);
        $expense = ExpenseService::approve($expense,$this->ctx);
        return response()->json($expense);
    }

    public function complete(string $id)
    {
        $expense = Expense::findOrFail($id);
        if (!$this->policy->complete($expense,$this->ctx)) return response()->json(['message'=>'auth.permission_denied'],403);
        $expense = ExpenseService::complete($expense,$this->ctx);
        return response()->json($expense);
    }

    public function reject(string $id)
    {
        $expense = Expense::findOrFail($id);
        if (!$this->policy->reject($expense,$this->ctx)) return response()->json(['message'=>'auth.permission_denied'],403);
        $expense = ExpenseService::reject($expense,$this->ctx);
        return response()->json($expense);
    }

    public function review(Request $request, string $id)
    {
        $expense = Expense::findOrFail($id);
        if (!$this->policy->review($expense,$this->ctx)) return response()->json(['message'=>'auth.permission_denied'],403);
        $request->validate(['approver_membership_id'=>'required|uuid']);
        $expense = ExpenseService::review($expense,$this->ctx,$request->approver_membership_id);
        return response()->json($expense);
    }
}
