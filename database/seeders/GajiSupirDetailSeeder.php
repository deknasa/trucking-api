<?php

namespace Database\Seeders;

use App\Models\GajiSupirDetail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GajiSupirDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete GajiSupirDetail");
        DB::statement("DBCC CHECKIDENT ('GajiSupirDetail', RESEED, 1);");


    }
}
