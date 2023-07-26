<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PelunasanPiutangDetail;
use Illuminate\Support\Facades\DB;

class PelunasanPiutangDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete pelunasanpiutangdetail");
        DB::statement("DBCC CHECKIDENT ('pelunasanpiutangdetail', RESEED, 1);");

    }
}
