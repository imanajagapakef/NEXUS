<?php

namespace App\Policies;

use App\Models\Expense;
use App\Support\OrganizationContext;

class ExpensePolicy
{
    private function has(string $perm, OrganizationContext $ctx): bool { return $ctx->hasPermission($perm); }
    private function sameOrg(Expense $e, OrganizationContext $ctx): bool { return $e->organization_id === $ctx->currentOrganizationId(); }

    public function create(OrganizationContext $ctx): bool { return $ctx->isActive() && $this->has('expense.create',$ctx); }
    public function view(Expense $e, OrganizationContext $ctx): bool { return $ctx->isActive() && $this->has('expense.read',$ctx) && $this->sameOrg($e,$ctx); }
    public function update(Expense $e, OrganizationContext $ctx): bool { return $ctx->isActive() && $this->has('expense.update',$ctx) && $this->sameOrg($e,$ctx) && $e->status==='pending'; }
    public function delete(Expense $e, OrganizationContext $ctx): bool { return $ctx->isActive() && $this->has('expense.delete',$ctx) && $this->sameOrg($e,$ctx) && $e->status==='pending'; }
    public function submit(Expense $e, OrganizationContext $ctx): bool { return $ctx->isActive() && $this->has('expense.submit',$ctx) && $this->sameOrg($e,$ctx) && $e->status==='pending'; }
    public function review(Expense $e, OrganizationContext $ctx): bool { return $ctx->isActive() && $this->has('expense.review',$ctx) && $this->sameOrg($e,$ctx) && $e->status==='pending'; }
    public function approve(Expense $e, OrganizationContext $ctx): bool { return $ctx->isActive() && $this->has('expense.approve',$ctx) && $this->sameOrg($e,$ctx) && $e->status==='pending'; }
    public function reject(Expense $e, OrganizationContext $ctx): bool {
        if (!$ctx->isActive() || !$this->has('expense.reject',$ctx) || !$this->sameOrg($e,$ctx) || $e->status!=='pending') return false;
        $mid = $ctx->currentMembershipId();
        return is_null($e->reviewer_membership_id) || $e->reviewer_membership_id===$mid || is_null($e->approver_membership_id) || $e->approver_membership_id===$mid;
    }
    public function complete(Expense $e, OrganizationContext $ctx): bool { return $ctx->isActive() && $this->has('expense.complete',$ctx) && $this->sameOrg($e,$ctx) && $e->status==='approved'; }
}
