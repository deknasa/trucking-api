<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GajiSupirPinjaman;
use Illuminate\Support\Facades\DB;

class GajiSupirPinjamanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete GajiSupirPinjaman");
        DB::statement("DBCC CHECKIDENT ('GajiSupirPinjaman', RESEED, 1);");
    }
}
