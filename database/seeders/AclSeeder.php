<?php

namespace Database\Seeders;

use App\Models\Acl;
use Illuminate\Database\Seeder;

class AclSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $acls = [
            [
                'aco_id' => 1,
                'role_id' => 1,
            ],
            [
                'aco_id' => 2,
                'role_id' => 1,
            ],
            [
                'aco_id' => 3,
                'role_id' => 1,
            ],
            [
                'aco_id' => 4,
                'role_id' => 1,
            ],
        ];

        for ($i = 1; $i <= 57; $i++) {
            Acl::create([
                'aco_id' => $i,
                'role_id' => 1,
                'modifiedby' => 'admin', 
            ]);
        }
    }
}
