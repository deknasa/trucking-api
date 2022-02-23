<?php

namespace Database\Seeders;
use App\Models\KotaRitasi;
use Illuminate\Database\Seeder;

class KotaRitasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        KotaRitasi::create([
            'kodekota' => 'AMPLAS',
            'keterangan' => 'AMPLAS',
            'statusaktif' => 1,
            'modifiedby' => 'ADMIN',
        ]);

        KotaRitasi::create([
            'kodekota' => 'PAKAM',
            'keterangan' => 'LUBUK PAKAM',
            'statusaktif' => 1,
            'modifiedby' => 'ADMIN',
        ]);

    }
}
