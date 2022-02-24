<?php

namespace Database\Seeders;

use App\Models\Cabang;
use Illuminate\Database\Seeder;

class CabangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Cabang::create([
            'kodecabang' => '',
            'namacabang' => '',
            'statusaktif' => '',
            'modifiedby' => 'ADMIN',
        ]);
    }
}
