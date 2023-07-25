<?php

namespace Database\Seeders;

use App\Models\JurnalUmumDetail;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JurnalUmumDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete jurnalumumdetail");
        DB::statement("DBCC CHECKIDENT ('jurnalumumdetail', RESEED, 1);");



        
    }
}
