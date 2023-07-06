<?php

namespace Database\Seeders;

use App\Models\dataritasi;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DataRitasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete dataritasi");
        DB::statement("DBCC CHECKIDENT ('dataritasi', RESEED, 1);");

        dataritasi::create(['statusritasi' => '91', 'nominal' => '6800.00', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        dataritasi::create(['statusritasi' => '92', 'nominal' => '6800.00', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        dataritasi::create(['statusritasi' => '272', 'nominal' => '0.00', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
    }
}
