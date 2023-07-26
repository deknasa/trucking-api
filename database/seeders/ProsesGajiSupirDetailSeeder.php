<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProsesGajiSupirDetail;
use Illuminate\Support\Facades\DB;

class ProsesGajiSupirDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete ProsesGajiSupirDetail");
        DB::statement("DBCC CHECKIDENT ('ProsesGajiSupirDetail', RESEED, 1);");

       
    }
}
