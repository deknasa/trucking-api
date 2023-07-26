<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GajiSupirHeader;
use Illuminate\Support\Facades\DB;

class GajiSupirHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete GajiSupirHeader");
        DB::statement("DBCC CHECKIDENT ('GajiSupirHeader', RESEED, 1);");



                
    }
}
