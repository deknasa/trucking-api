<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = [
            [
                'rolename' => 'ADMIN',
            ],
        ];

        foreach ($roles as $role ) {
            Role::create([
                'rolename' => $role['rolename']
            ]);
        }
    }
}
