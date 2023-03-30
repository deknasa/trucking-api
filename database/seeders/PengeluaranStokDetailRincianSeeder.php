<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use App\Models\PengeluaranStokDetailRincian;
use Illuminate\Support\Facades\DB;

class PengeluaranStokDetailRincianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete PengeluaranStokDetailRincian");
        DB::statement("DBCC CHECKIDENT ('PengeluaranStokDetailRincian', RESEED, 1);");

      
    }
}
