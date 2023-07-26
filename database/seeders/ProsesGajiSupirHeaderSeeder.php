<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProsesGajiSupirHeader;
use Illuminate\Support\Facades\DB;

class ProsesGajiSupirHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete prosesgajisupirheader");
        DB::statement("DBCC CHECKIDENT ('prosesgajisupirheader', RESEED, 1);");

    

    }
}
