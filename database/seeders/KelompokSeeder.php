<?php

namespace Database\Seeders;

use App\Models\Kelompok;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KelompokSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete Kelompok");
        DB::statement("DBCC CHECKIDENT ('Kelompok', RESEED, 1);");

        Kelompok::create(['kodekelompok' => 'BAN', 'keterangan' => 'KELOMPOK UNTUK BAN', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        Kelompok::create(['kodekelompok' => 'SPAREPART', 'keterangan' => 'KELOMPOK UNTUK SPAREPART', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        Kelompok::create(['kodekelompok' => 'AKI', 'keterangan' => 'KELOMPOK UNTUK AKI', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        Kelompok::create(['kodekelompok' => 'PERALATAN', 'keterangan' => 'KELOMPOK UNTUK PERALATAN', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
    }
}
