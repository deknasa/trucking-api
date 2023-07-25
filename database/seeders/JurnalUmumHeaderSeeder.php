<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JurnalUmumHeader;
use Illuminate\Support\Facades\DB;

class JurnalUmumHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete jurnalumumheader");
        DB::statement("DBCC CHECKIDENT ('jurnalumumheader', RESEED, 1);");


    }
}
