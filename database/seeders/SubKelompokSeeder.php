<?php

namespace Database\Seeders;

use App\Models\Subkelompok;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubKelompokSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete Subkelompok");
        DB::statement("DBCC CHECKIDENT ('Subkelompok', RESEED, 1);");

        Subkelompok::create(['kodesubkelompok' => 'BAUT', 'keterangan' => 'BAUT', 'kelompok_id' => '2', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        Subkelompok::create(['kodesubkelompok' => 'MUR', 'keterangan' => 'MUR', 'kelompok_id' => '2', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        Subkelompok::create(['kodesubkelompok' => 'RADIATOR', 'keterangan' => 'RADIATOR', 'kelompok_id' => '2', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
    }
}
