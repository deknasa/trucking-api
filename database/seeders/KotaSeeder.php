<?php

namespace Database\Seeders;
use App\Models\Kota;
use Illuminate\Database\Seeder;

class KotaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Kota::create([
            'kodekota' => 'BLW',
            'keterangan' => 'BELAWAN',
            'zona_id' => 1,
            'statusaktif' => 1,
            'modifiedby' => 'ADMIN',
        ]);

        Kota::create([
            'kodekota' => 'AMPLAS',
            'keterangan' => 'AMPLAS',
            'zona_id' => 1,
            'statusaktif' => 1,
            'modifiedby' => 'ADMIN',
        ]);

        Kota::create([
            'kodekota' => 'PAKAM',
            'keterangan' => 'LUBUK PAKAM',
            'zona_id' => 1,
            'statusaktif' => 1,
            'modifiedby' => 'ADMIN',
        ]);
    }
}
