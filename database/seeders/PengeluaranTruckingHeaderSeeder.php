<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PengeluaranTruckingHeader;
use Illuminate\Support\Facades\DB;


class PengeluaranTruckingHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete pengeluarantruckingheader");
        DB::statement("DBCC CHECKIDENT ('pengeluarantruckingheader', RESEED, 1);");
        

    }
}
