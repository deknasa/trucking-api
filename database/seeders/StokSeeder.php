<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Stok;
use Illuminate\Support\Facades\DB;

class StokSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete Stok");
        DB::statement("DBCC CHECKIDENT ('Stok', RESEED, 1);");

        Stok::create(['jenistrado_id' => '0', 'kelompok_id' => '2', 'subkelompok_id' => '1', 'kategori_id' => '3', 'merk_id' => '0', 'conv1' => '1', 'conv2' => '1', 'namastok' => 'BAUT 12', 'statusaktif' => '1', 'qtymin' => '20', 'qtymax' => '0', 'keterangan' => '', 'gambar' => '', 'namaterpusat' => 'BAUT 12', 'modifiedby' => 'ADMIN',]);
        Stok::create(['jenistrado_id' => '0', 'kelompok_id' => '2', 'subkelompok_id' => '3', 'kategori_id' => '5', 'merk_id' => '0', 'conv1' => '1', 'conv2' => '1', 'namastok' => 'RADIATOR', 'statusaktif' => '1', 'qtymin' => '1', 'qtymax' => '1', 'keterangan' => '', 'gambar' => '', 'namaterpusat' => 'RADIATOR', 'modifiedby' => 'ADMIN',]);
    }
}
