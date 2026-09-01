<?php

namespace App\Services\Expense;

use App\Models\Expense;
use App\Services\AuditService;
use App\Services\NotificationService;
use App\Support\OrganizationContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ExpenseService
{
    public static function create(array $data, OrganizationContext $ctx): Expense
    {
        return DB::transaction(function () use ($data, $ctx) {
            $expense = Expense::create([
                'id' => (string) Str::uuid(),
                'organization_id' => $ctx->currentOrganizationId(),
                'creator_membership_id' => $ctx->currentMembershipId(),
                'description' => $data['description'],
                'amount' => $data['amount'],
                'status' => 'pending',
            ]);
            AuditService::record('CREATE_EXPENSE','expense',$expense->id,$ctx);
            return $expense;
        });
    }

    public static function submit(Expense $expense, OrganizationContext $ctx, string $reviewerMembershipId): Expense
    {
        return DB::transaction(function () use ($expense, $ctx, $reviewerMembershipId) {
            $expense = Expense::where('id',$expense->id)->lockForUpdate()->firstOrFail();
            if ($expense->status !== 'pending') abort(422,'expense.invalid_transition');
            AuditService::record('REVIEW_EXPENSE','expense',$expense->id,$ctx);
            NotificationService::create($ctx->currentOrganizationId(), $reviewerMembershipId, 'Expense pending review', "Expense {$expense->id} submitted");
            return $expense;
        });
    }

    public static function review(Expense $expense, OrganizationContext $ctx, string $approverMembershipId): Expense
    {
        return DB::transaction(function () use ($expense, $ctx, $approverMembershipId) {
            $expense = Expense::where('id',$expense->id)->lockForUpdate()->firstOrFail();
            if ($expense->status !== 'pending') abort(422,'expense.invalid_transition');
            $expense->update(['reviewer_membership_id'=>$ctx->currentMembershipId()]);
            AuditService::record('REVIEW_EXPENSE','expense',$expense->id,$ctx);
            NotificationService::create($ctx->currentOrganizationId(), $approverMembershipId, 'Expense ready for approval', "Expense {$expense->id} reviewed");
            return $expense;
        });
    }

    public static function reject(Expense $expense, OrganizationContext $ctx): Expense
    {
        return DB::transaction(function () use ($expense, $ctx) {
            $expense = Expense::where('id',$expense->id)->lockForUpdate()->firstOrFail();
            if ($expense->status !== 'pending') abort(422,'expense.invalid_transition');
            $expense->update(['status'=>'rejected']);
            AuditService::record('REJECT_EXPENSE','expense',$expense->id,$ctx);
            NotificationService::create($ctx->currentOrganizationId(), $expense->creator_membership_id, 'Expense rejected', "Expense {$expense->id} rejected");
            return $expense;
        });
    }

    public static function approve(Expense $expense, OrganizationContext $ctx): Expense
    {
        return DB::transaction(function () use ($expense, $ctx) {
            $expense = Expense::where('id',$expense->id)->lockForUpdate()->firstOrFail();
            if ($expense->status !== 'pending') abort(422,'expense.invalid_transition');
            $expense->update(['status'=>'approved']);
            AuditService::record('APPROVE_EXPENSE','expense',$expense->id,$ctx);
            NotificationService::create($ctx->currentOrganizationId(), $expense->creator_membership_id, 'Expense approved', "Expense {$expense->id} approved");
            return $expense;
        });
    }

    public static function complete(Expense $expense, OrganizationContext $ctx): Expense
    {
        return DB::transaction(function () use ($expense, $ctx) {
            $expense = Expense::where('id',$expense->id)->lockForUpdate()->firstOrFail();
            if ($expense->status !== 'approved') abort(422,'expense.invalid_transition');
            $expense->update(['status'=>'paid']);
            AuditService::record('COMPLETE_EXPENSE','expense',$expense->id,$ctx);
            NotificationService::create($ctx->currentOrganizationId(), $expense->creator_membership_id, 'Payment completed', "Expense {$expense->id} paid");
            return $expense;
        });
    }
}
