<?php

namespace Database\Seeders;

use App\Models\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete userrole");
        DB::statement("DBCC CHECKIDENT ('userrole', RESEED, 0);");

        userrole::create(['user_id' => '2',  'role_id' => '1',  'modifiedby' => 'ADMIN',]);
        userrole::create(['user_id' => '2',  'role_id' => '2',  'modifiedby' => 'ADMIN',]);
        userrole::create(['user_id' => '1',  'role_id' => '1',  'modifiedby' => 'ADMIN',]);
        userrole::create(['user_id' => '1',  'role_id' => '2',  'modifiedby' => 'ADMIN',]);
    }
}
