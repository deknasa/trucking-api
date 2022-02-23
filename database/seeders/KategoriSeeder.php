<?php

namespace Database\Seeders;
use App\Models\Kategori;

use Illuminate\Database\Seeder;

class KategoriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Kategori::create([
            'kodekategori' => 'BAUT RODA',
            'subkelompok_id' => 1,
            'keterangan' => 'UNTUK BAUT RODA',
            'statusaktif' => 1,
            'modifiedby' => 'ADMIN',
        ]);

        Kategori::create([
            'kodekategori' => 'MUR RODA',
            'subkelompok_id' => 2,
            'keterangan' => 'UNTUK MUR RODA',
            'statusaktif' => 1,
            'modifiedby' => 'ADMIN',
        ]);

    }
}
