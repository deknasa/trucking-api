<?php

namespace Database\Seeders;

<<<<<<< HEAD
use App\Models\UserRole;
=======
>>>>>>> 2a180d1c8113d21ff9e5eb8d26b0e92eeedc32c0
use Illuminate\Database\Seeder;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
<<<<<<< HEAD
        $userroles  = [
            [
                'user_id' => 1,
                'role_id' => 1,
            ],
            [
                'user_id' => 1,
                'role_id' => 28,
            ],
            [
                'user_id' => 2,
                'role_id' => 1,
            ]
        ];
        
        foreach ($userroles as $userrole) {
            UserRole::create([
                'user_id' => $userrole['user_id'],
                'role_id' => $userrole['role_id'],
                'modifiedby' => 'admin',
            ]);
        }
=======
        //
>>>>>>> 2a180d1c8113d21ff9e5eb8d26b0e92eeedc32c0
    }
}
