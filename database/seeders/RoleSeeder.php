<?php

namespace Database\Seeders;

<<<<<<< HEAD
use App\Models\Role;
=======
>>>>>>> 2a180d1c8113d21ff9e5eb8d26b0e92eeedc32c0
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
<<<<<<< HEAD
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
=======
        //
>>>>>>> 2a180d1c8113d21ff9e5eb8d26b0e92eeedc32c0
    }
}
