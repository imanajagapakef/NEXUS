<?php

namespace Tests\Feature;

use App\Models\Membership;
use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_invalid_credentials(): void
    {
        $response = $this->postJson('/login', ['email'=>'no@no.com','password'=>'wrong']);
        $response->assertStatus(422)->assertJson(['message'=>'auth.invalid_credentials']);
    }

    public function test_login_no_membership(): void
    {
        $user = User::create(['id'=>Str::uuid()->toString(),'name'=>'U','email'=>'u@test.com','password'=>Hash::make('password')]);
        $response = $this->postJson('/login', ['email'=>'u@test.com','password'=>'password']);
        $response->assertStatus(422)->assertJson(['message'=>'auth.no_organization']);
    }

    public function test_login_inactive_membership(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $org = Organization::create(['id'=>Str::uuid()->toString(),'name'=>'Org'.Str::random(5)]);
        $user = User::create(['id'=>Str::uuid()->toString(),'name'=>'U','email'=>'u@test.com','password'=>Hash::make('password')]);
        $role = Role::where('name','Viewer')->first();
        Membership::create(['id'=>Str::uuid()->toString(),'user_id'=>$user->id,'organization_id'=>$org->id,'role_id'=>$role->id,'joined_at'=>now(),'status'=>'inactive']);
        $this->postJson('/login', ['email'=>'u@test.com','password'=>'password']);
        $response = $this->postJson('/select-organization', ['organization_id'=>$org->id]);
        $response->assertStatus(403)->assertJson(['message'=>'auth.inactive_membership']);
    }
}
