<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GajiSupirBbm;
use Illuminate\Support\Facades\DB;

class GajiSupirBbmSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete GajiSupirBbm");
        DB::statement("DBCC CHECKIDENT ('GajiSupirBbm', RESEED, 1);");



    }
}
