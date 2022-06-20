<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Gudang;

class GudangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Gudang::create([
            'gudang' => 'GUDANG KANTOR',
            'statusaktif' => 1,
            'modifiedby' => 'ADMIN',
        ]);
        Gudang::create([
            'gudang' => 'GUDANG PIHAK III',
            'statusaktif' => 1,
            'modifiedby' => 'ADMIN',
        ]);

        Gudang::create([
            'gudang' => 'GUDANG SEMENTARA',
            'statusaktif' => 1,
            'modifiedby' => 'ADMIN',
        ]);

    }
}
