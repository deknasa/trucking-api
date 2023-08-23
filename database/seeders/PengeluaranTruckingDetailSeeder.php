<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PengeluaranTruckingDetail;
use Illuminate\Support\Facades\DB;

class PengeluaranTruckingDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete pengeluarantruckingdetail");
        DB::statement("DBCC CHECKIDENT ('pengeluarantruckingdetail', RESEED, 1);");

    }
}
