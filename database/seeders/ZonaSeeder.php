<?php

namespace Database\Seeders;

use App\Models\Zona;
use Illuminate\Database\Seeder;

class ZonaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        Zona::create([
            'zona' => 'ZONA 2',
            'keterangan' => 'ZONA 2',
            'statusaktif' => 1,
            'modifiedby' => 'ADMIN',
        ]);
    }
}
