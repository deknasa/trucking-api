<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SuratPengantar;
use Illuminate\Support\Facades\DB;

class SuratPengantarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete SuratPengantar");
        DB::statement("DBCC CHECKIDENT ('SuratPengantar', RESEED, 1);");

       
           }
}
