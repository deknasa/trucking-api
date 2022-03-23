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
            'kodecabang' => 'PST',
            'namacabang' => 'PUSAT',
            'statusaktif' => 1,
            'modifiedby' => 'ADMIN',
        ]);
        Cabang::create([
            'kodecabang' => 'JKT',
            'namacabang' => 'JAKARTA',
            'statusaktif' => 1,
            'modifiedby' => 'ADMIN',
        ]);
        Cabang::create([
            'kodecabang' => 'MDN',
            'namacabang' => 'MEDAN',
            'statusaktif' => 1,
            'modifiedby' => 'ADMIN',
        ]);
        Cabang::create([
            'kodecabang' => 'SBY',
            'namacabang' => 'SURABAYA',
            'statusaktif' => 1,
            'modifiedby' => 'ADMIN',
        ]);
        Cabang::create([
            'kodecabang' => 'MKS',
            'namacabang' => 'MAKASSAR',
            'statusaktif' => 1,
            'modifiedby' => 'ADMIN',
        ]);
    }
}
