<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Owner','Admin','Manager','Staff','Viewer'] as $name) {
            DB::table('roles')->updateOrInsert(['name'=>$name], ['id'=>Str::uuid()->toString(),'name'=>$name,'created_at'=>now(),'updated_at'=>now()]);
        }
    }
}
