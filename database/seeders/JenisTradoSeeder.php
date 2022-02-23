<?php

namespace Database\Seeders;
use App\Models\JenisTrado;
use Illuminate\Database\Seeder;

class JenisTradoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        JenisTrado::create([
            'kodejenistrado' => 'ALL',
            'keterangan' => 'UNTUK ALL TRUCK',
            'statusaktif' => 1,
            'modifiedby' => 'ADMIN',
        ]);

        JenisTrado::create([
            'kodejenistrado' => 'HINO',
            'keterangan' => 'UNTUK TRAILER HINO',
            'statusaktif' => 1,
            'modifiedby' => 'ADMIN',
        ]);

    }
}
