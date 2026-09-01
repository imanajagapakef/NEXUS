<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\Membership;
use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use App\Services\Expense\ExpenseService;
use App\Support\OrganizationContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ExpenseLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private function setupOrg(): array
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        $org = Organization::create(['id'=>Str::uuid()->toString(),'name'=>'Org'.Str::random(5)]);
        $user = User::create(['id'=>Str::uuid()->toString(),'name'=>'U','email'=>'u'.Str::random(5).'@test.com','password'=>bcrypt('password')]);
        $role = Role::where('name','Owner')->first();
        $mem = Membership::create(['id'=>Str::uuid()->toString(),'user_id'=>$user->id,'organization_id'=>$org->id,'role_id'=>$role->id,'joined_at'=>now(),'status'=>'active']);
        $ctx = new OrganizationContext();
        $perms = ['expense.create','expense.read','expense.submit','expense.review','expense.approve','expense.reject','expense.complete'];
        $ctx->set($user,$org,$mem,$perms);
        return [$org,$user,$mem,$ctx];
    }

    public function test_create_and_state_flow(): void
    {
        [$org,$user,$mem,$ctx] = $this->setupOrg();
        $exp = ExpenseService::create(['description'=>'Test','amount'=>100], $ctx);
        $this->assertEquals('pending',$exp->status);
        $exp = ExpenseService::approve($exp, $ctx);
        $this->assertEquals('approved',$exp->status);
        $exp = ExpenseService::complete($exp, $ctx);
        $this->assertEquals('paid',$exp->status);
    }

    public function test_reject_from_pending(): void
    {
        [$org,$user,$mem,$ctx] = $this->setupOrg();
        $exp = ExpenseService::create(['description'=>'Test','amount'=>50], $ctx);
        $exp = ExpenseService::reject($exp, $ctx);
        $this->assertEquals('rejected',$exp->status);
    }

    public function test_forbidden_transition(): void
    {
        [$org,$user,$mem,$ctx] = $this->setupOrg();
        $exp = ExpenseService::create(['description'=>'Test','amount'=>50], $ctx);
        $exp->update(['status'=>'rejected']);
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        ExpenseService::complete($exp, $ctx);
    }
}
