<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Expense;
use App\Models\Membership;
use App\Models\Notification;
use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use App\Services\Expense\ExpenseService;
use App\Support\OrganizationContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuditNotificationTest extends TestCase
{
    use RefreshDatabase;

    private function ctx(): OrganizationContext
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        $org = Organization::create(['id'=>Str::uuid()->toString(),'name'=>'Org'.Str::random(5)]);
        $user = User::create(['id'=>Str::uuid()->toString(),'name'=>'U','email'=>Str::random(5).'@test.com','password'=>bcrypt('password')]);
        $role = Role::where('name','Owner')->first();
        $mem = Membership::create(['id'=>Str::uuid()->toString(),'user_id'=>$user->id,'organization_id'=>$org->id,'role_id'=>$role->id,'joined_at'=>now(),'status'=>'active']);
        $ctx = new OrganizationContext();
        $ctx->set($user,$org,$mem,['expense.create','expense.read','expense.submit','expense.review','expense.approve','expense.reject','expense.complete']);
        return $ctx;
    }

    public function test_audit_and_notification_on_create(): void
    {
        $ctx = $this->ctx();
        $exp = ExpenseService::create(['description'=>'Audit test','amount'=>123.45], $ctx);
        $this->assertDatabaseHas('audit_logs',['action'=>'CREATE_EXPENSE','entity_id'=>$exp->id]);
        // submit creates notification to reviewer
        $org2 = $ctx->organization;
        $user2 = User::create(['id'=>Str::uuid()->toString(),'name'=>'R','email'=>Str::random(5).'@test.com','password'=>bcrypt('password')]);
        $role2 = Role::where('name','Staff')->first();
        $mem2 = Membership::create(['id'=>Str::uuid()->toString(),'user_id'=>$user2->id,'organization_id'=>$org2->id,'role_id'=>$role2->id,'joined_at'=>now(),'status'=>'active']);
        ExpenseService::submit($exp, $ctx, $mem2->id);
        $this->assertDatabaseHas('notifications',['membership_id'=>$mem2->id,'organization_id'=>$org2->id]);
        $this->assertDatabaseHas('audit_logs',['action'=>'REVIEW_EXPENSE']);
    }

    public function test_notification_cascade_on_membership_delete_blocked(): void
    {
        $ctx = $this->ctx();
        $exp = ExpenseService::create(['description'=>'Cascade test','amount'=>10], $ctx);
        // notifications are subordinate — but membership delete should be RESTRICT for expenses
        $this->expectException(\Illuminate\Database\QueryException::class);
        // Try to delete creator membership while expense exists — should fail RESTRICT
        \Illuminate\Support\Facades\DB::table('memberships')->where('id',$ctx->membership->id)->delete();
    }
}
