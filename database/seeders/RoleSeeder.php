<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {


        DB::statement("delete Role");
        DB::statement("DBCC CHECKIDENT ('Role', RESEED, 1);");

        Role::create(['rolename' => 'ADMIN',  'modifiedby' => 'ADMIN',]);
        Role::create(['rolename' => 'TEST',  'modifiedby' => 'ADMIN',]);
    }
}
