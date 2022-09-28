<?php

namespace Database\Seeders;

use App\Models\Zona;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ZonaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete zona");
        DB::statement("DBCC CHECKIDENT ('zona', RESEED, 0);");

        zona::create(['zona' => 'luar kota',  'keterangan' => 'luar kota',  'statusaktif' => '1',  'modifiedby' => 'ADMIN',]);
    }
}
