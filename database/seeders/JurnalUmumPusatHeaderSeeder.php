<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JurnalUmumPusatHeader;
use Illuminate\Support\Facades\DB;

class JurnalUmumPusatHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete jurnalumumpusatheader");
        DB::statement("DBCC CHECKIDENT ('jurnalumumpusatheader', RESEED, 1);");

        
    }
}
