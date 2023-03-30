<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PengeluaranStokHeader;
use Illuminate\Support\Facades\DB;

class PengeluaranStokHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {



        DB::statement("delete PengeluaranStokHeader");
        DB::statement("DBCC CHECKIDENT ('PengeluaranStokHeader', RESEED, 1);");

       
    }
}
