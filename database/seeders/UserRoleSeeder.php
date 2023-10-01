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
        DB::statement("DBCC CHECKIDENT ('userrole', RESEED, 1);");

        userrole::create([ 'user_id' => '1', 'role_id' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
        userrole::create([ 'user_id' => '1', 'role_id' => '2', 'modifiedby' => 'ADMIN', 'info' => '',]);
        userrole::create([ 'user_id' => '2', 'role_id' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
        userrole::create([ 'user_id' => '7', 'role_id' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
        userrole::create([ 'user_id' => '3', 'role_id' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
        userrole::create([ 'user_id' => '5', 'role_id' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
        userrole::create([ 'user_id' => '4', 'role_id' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
        userrole::create([ 'user_id' => '6', 'role_id' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
        userrole::create([ 'user_id' => '8', 'role_id' => '3', 'modifiedby' => 'ADMIN', 'info' => '',]);
        userrole::create([ 'user_id' => '11', 'role_id' => '6', 'modifiedby' => 'ADMIN', 'info' => '',]);
        userrole::create([ 'user_id' => '15', 'role_id' => '8', 'modifiedby' => 'ADMIN', 'info' => '',]);
        userrole::create([ 'user_id' => '18', 'role_id' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
        userrole::create([ 'user_id' => '19', 'role_id' => '6', 'modifiedby' => 'RYAN', 'info' => '',]);
        userrole::create([ 'user_id' => '9', 'role_id' => '4', 'modifiedby' => 'AGNES', 'info' => '',]);
        userrole::create([ 'user_id' => '10', 'role_id' => '4', 'modifiedby' => 'AGNES', 'info' => '',]);
        userrole::create([ 'user_id' => '13', 'role_id' => '6', 'modifiedby' => 'AGNES', 'info' => '',]);
        userrole::create([ 'user_id' => '12', 'role_id' => '6', 'modifiedby' => 'AGNES', 'info' => '',]);
        userrole::create([ 'user_id' => '14', 'role_id' => '6', 'modifiedby' => 'AGNES', 'info' => '',]);
        userrole::create([ 'user_id' => '17', 'role_id' => '9', 'modifiedby' => 'AGNES', 'info' => '',]);
        userrole::create([ 'user_id' => '20', 'role_id' => '7', 'modifiedby' => 'RYAN', 'info' => '',]);
        userrole::create([ 'user_id' => '16', 'role_id' => '7', 'modifiedby' => 'AGNES', 'info' => '',]);    }
}
