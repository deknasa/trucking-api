<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GajiSupirPelunasanPinjaman;
use Illuminate\Support\Facades\DB;

class GajiSupirPelunasanPinjamanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete GajiSupirPelunasanPinjaman");
        DB::statement("DBCC CHECKIDENT ('GajiSupirPelunasanPinjaman', RESEED, 1);");


    }
}
