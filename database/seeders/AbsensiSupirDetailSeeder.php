<?php

namespace Database\Seeders;

use App\Models\AbsensiSupirDetail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AbsensiSupirDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete AbsensiSupirDetail");
        DB::statement("DBCC CHECKIDENT ('AbsensiSupirDetail', RESEED, 1);");


    }
}
