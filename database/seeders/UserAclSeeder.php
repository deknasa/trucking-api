<?php

namespace Database\Seeders;

use App\Models\UserAcl;
use Illuminate\Database\Seeder;

class UserAclSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $userAcls = [
            [
                'aco_id' => 1,
                'user_id' => 1,
            ],
            [
                'aco_id' => 2,
                'user_id' => 1,
            ],
            [
                'aco_id' => 3,
                'user_id' => 1,
            ],
            [
                'aco_id' => 4,
                'user_id' => 1,
            ],
            [
                'aco_id' => 5,
                'user_id' => 1,
            ],
            [
                'aco_id' => 6,
                'user_id' => 7,
            ],
            [
                'aco_id' => 8,
                'user_id' => 9,
            ],
            [
                'aco_id' => 10,
                'user_id' => 1,
            ],
            [
                'aco_id' => 11,
                'user_id' => 1,
            ],
            [
                'aco_id' => 12,
                'user_id' => 1,
            ],
            [
                'aco_id' => 13,
                'user_id' => 1,
            ],
            [
                'aco_id' => 14,
                'user_id' => 1,
            ],
            [
                'aco_id' => 15,
                'user_id' => 1,
            ],
            [
                'aco_id' => 16,
                'user_id' => 1,
            ],
            [
                'aco_id' => 17,
                'user_id' => 1,
            ],
            [
                'aco_id' => 78,
                'user_id' => 1,
            ],
        ];

        foreach ($userAcls as $userAcl) {
            UserAcl::create([
                'aco_id' => $userAcl['aco_id'],
                'user_id' => $userAcl['user_id'],
                'modifiedby' => 'admin'
            ]);
        }
    }
}
