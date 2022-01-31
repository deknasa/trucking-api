<?php

namespace Database\Seeders;

use App\Models\UserRole;
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
    }
}
