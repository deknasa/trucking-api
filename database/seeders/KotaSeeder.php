<?php

namespace Database\Seeders;

use App\Models\Kota;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KotaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete Kota");
        DB::statement("DBCC CHECKIDENT ('Kota', RESEED, 1);");

        Kota::create(['kodekota' => 'BLW', 'keterangan' => 'BELAWAN', 'zona_id' => '1', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        Kota::create(['kodekota' => 'AMPLAS', 'keterangan' => 'AMPLAS', 'zona_id' => '1', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        Kota::create(['kodekota' => 'PAKAM', 'keterangan' => 'LUBUK PAKAM', 'zona_id' => '1', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        Kota::create(['kodekota' => 'SDFDS', 'keterangan' => 'DSFSDAF', 'zona_id' => '1', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
    }
}
