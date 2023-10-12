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

    }
}
