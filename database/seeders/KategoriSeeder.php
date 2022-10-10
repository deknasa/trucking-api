<?php

namespace Database\Seeders;

use App\Models\Kategori;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KategoriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete Kategori");
        DB::statement("DBCC CHECKIDENT ('Kategori', RESEED, 1);");

        Kategori::create(['kodekategori' => 'BAUT RODA', 'keterangan' => 'UNTUK BAUT RODA', 'subkelompok_id' => '1', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        Kategori::create(['kodekategori' => 'MUR RODA', 'keterangan' => 'UNTUK MUR RODA', 'subkelompok_id' => '2', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        Kategori::create(['kodekategori' => 'BAUT', 'keterangan' => 'BAUT', 'subkelompok_id' => '1', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        Kategori::create(['kodekategori' => 'RADIATOR', 'keterangan' => 'RADIATOR', 'subkelompok_id' => '3', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        Kategori::create(['kodekategori' => 'SPAREPART', 'keterangan' => 'SPAREPART', 'subkelompok_id' => '4', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        Kategori::create(['kodekategori' => 'BAN', 'keterangan' => 'BAN', 'subkelompok_id' => '5', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
    }
}
