<?php

namespace Database\Seeders;

use App\Models\KasGantungDetail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KasGantungDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete KasGantungDetail");
        DB::statement("DBCC CHECKIDENT ('KasGantungDetail', RESEED, 1);");

      

    }
}
