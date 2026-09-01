<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $perms = ['expense.create','expense.read','expense.update','expense.delete','expense.submit','expense.review','expense.approve','expense.reject','expense.complete'];
        foreach ($perms as $p) {
            DB::table('permissions')->updateOrInsert(['name'=>$p], ['id'=>Str::uuid()->toString(),'name'=>$p,'created_at'=>now(),'updated_at'=>now()]);
        }
    }
}
