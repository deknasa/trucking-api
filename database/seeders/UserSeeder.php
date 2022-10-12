<?php

namespace Database\Seeders;

use App\Models\Cabang;
use App\Models\Parameter;
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
            'cabang_id' => Cabang::where('kodecabang', 'PST')->first()->id,
            'statusaktif' => Parameter::where('grp', 'STATUS AKTIF')->where('text', 'AKTIF')->first()->id,
            'password' => bcrypt('123456'),
        ]);
        
        User::factory()->create();
    }
}
