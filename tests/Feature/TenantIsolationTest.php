<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\Membership;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_cross_tenant_expense_access_denied(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $orgA = Organization::create(['id'=>Str::uuid()->toString(),'name'=>'OrgA']);
        $orgB = Organization::create(['id'=>Str::uuid()->toString(),'name'=>'OrgB']);
        $user = User::create(['id'=>Str::uuid()->toString(),'name'=>'U','email'=>'u@test.com','password'=>bcrypt('password')]);
        $roleViewer = Role::where('name','Viewer')->first();
        $memA = Membership::create(['id'=>Str::uuid()->toString(),'user_id'=>$user->id,'organization_id'=>$orgA->id,'role_id'=>$roleViewer->id,'joined_at'=>now(),'status'=>'active']);
        $memB = Membership::create(['id'=>Str::uuid()->toString(),'user_id'=>$user->id,'organization_id'=>$orgB->id,'role_id'=>$roleViewer->id,'joined_at'=>now(),'status'=>'active']);

        // Try to create expense in orgB using memA context should fail via DB FK
        $this->expectException(\Illuminate\Database\QueryException::class);
        Expense::create([
            'id'=>Str::uuid()->toString(),
            'organization_id'=>$orgB->id,
            'creator_membership_id'=>$memA->id, // cross-tenant!
            'description'=>'x','amount'=>10,'status'=>'pending'
        ]);
    }

    public function test_expense_state_machine(): void
    {
        $exp = new Expense(['status'=>'pending']);
        $this->assertTrue($exp->canTransitionTo('approved'));
        $this->assertTrue($exp->canTransitionTo('rejected'));
        $this->assertFalse($exp->canTransitionTo('paid'));
        $exp2 = new Expense(['status'=>'approved']);
        $this->assertTrue($exp2->canTransitionTo('paid'));
        $this->assertFalse($exp2->canTransitionTo('rejected'));
    }
}
