<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'user' => 'ADMIN',
            'name' => 'ADMIN',
            'password' => bcrypt('123456'),
        ]);
        
        User::factory()->create();
    }
}
