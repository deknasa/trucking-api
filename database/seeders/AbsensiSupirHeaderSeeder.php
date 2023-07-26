<?php

namespace Database\Seeders;

use App\Models\AbsensiSupirHeader;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AbsensiSupirHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete AbsensiSupirHeader");
        DB::statement("DBCC CHECKIDENT ('AbsensiSupirHeader', RESEED, 1);");

    }
}
