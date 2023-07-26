<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PelunasanPiutangHeader;
use Illuminate\Support\Facades\DB;

class PelunasanPiutangHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete pelunasanpiutangheader");
        DB::statement("DBCC CHECKIDENT ('pelunasanpiutangheader', RESEED, 1);");

    }
}
