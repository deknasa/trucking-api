<?php

namespace Database\Seeders;

use App\Models\Aco;
use Illuminate\Database\Seeder;

class AcoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $acos = [
            [
                'class' => 'user',
                'method' => 'index',
                'nama' => 'Master User'
            ],
            [
                'class' => 'user',
                'method' => 'create',
                'nama' => 'Master User'
            ],
            [
                'class' => 'user',
                'method' => 'edit',
                'nama' => 'Master User'
            ],
            [
                'class' => 'user',
                'method' => 'delete',
                'nama' => 'Master User'
            ],
            [
                'class' => 'parameter',
                'method' => 'index',
                'nama' => 'Master Parameter'
            ],
            [
                'class' => 'parameter',
                'method' => 'create',
                'nama' => 'Master Parameter'
            ],
            [
                'class' => 'parameter',
                'method' => 'edit',
                'nama' => 'Master Parameter'
            ],
            [
                'class' => 'parameter',
                'method' => 'delete',
                'nama' => 'Master Parameter'
            ],
        ];

        foreach ($acos as $aco) {
            Aco::create([
                'class' => $aco['class'],
                'method' => $aco['method'],
                'nama' => $aco['nama'],
                'modifiedby' => 'admin'
            ]);
        }
    }
}
