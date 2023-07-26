<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SuratPengantarBiayaTambahan;
use Illuminate\Support\Facades\DB;

class SuratPengantarBiayaTambahanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete suratpengantarbiayatambahan");
        DB::statement("DBCC CHECKIDENT ('suratpengantarbiayatambahan', RESEED, 1);");


    }
}
