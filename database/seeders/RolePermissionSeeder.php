<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $matrix = [
            'Owner'=>['expense.create','expense.read','expense.update','expense.delete','expense.submit','expense.review','expense.approve','expense.reject','expense.complete'],
            'Admin'=>['expense.create','expense.read','expense.update','expense.delete','expense.submit','expense.review','expense.approve','expense.reject','expense.complete'],
            'Manager'=>['expense.create','expense.read','expense.update','expense.delete','expense.submit','expense.review','expense.approve','expense.reject','expense.complete'],
            'Staff'=>['expense.create','expense.read','expense.update','expense.delete','expense.submit','expense.review','expense.reject'],
            'Viewer'=>['expense.read'],
        ];
        foreach ($matrix as $roleName=>$perms) {
            $roleId = DB::table('roles')->where('name',$roleName)->value('id');
            foreach ($perms as $permName) {
                $permId = DB::table('permissions')->where('name',$permName)->value('id');
                DB::table('role_permissions')->updateOrInsert(['role_id'=>$roleId,'permission_id'=>$permId], ['role_id'=>$roleId,'permission_id'=>$permId]);
            }
        }
    }
}
