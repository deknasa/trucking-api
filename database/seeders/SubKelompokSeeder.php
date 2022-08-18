<?php

namespace Database\Seeders;
use App\Models\Subkelompok;
use Illuminate\Database\Seeder;

class SubKelompokSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Subkelompok::create([
            'kodesubkelompok' => 'BAUT',
            'keterangan' => 'BAUT',
            'kelompok_id' => 2,
            'statusaktif' => 1,
            'modifiedby' => 'ADMIN',
        ]);

        Subkelompok::create([
            'kodesubkelompok' => 'MUR',
            'keterangan' => 'MUR',
            'kelompok_id' => 2,
            'statusaktif' => 1,
            'modifiedby' => 'ADMIN',
        ]);

        Subkelompok::create([
            'kodesubkelompok' => 'RADIATOR',
            'keterangan' => 'RADIATOR',
            'kelompok_id' => 2,
            'statusaktif' => 1,
            'modifiedby' => 'ADMIN',
        ]);
    }
}
