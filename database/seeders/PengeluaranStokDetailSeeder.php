<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PengeluaranStokDetail;
use Illuminate\Support\Facades\DB;

class PengeluaranStokDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {


        DB::statement("delete PengeluaranStokDetail");
        DB::statement("DBCC CHECKIDENT ('PengeluaranStokDetail', RESEED, 1);");

       
    }
}
