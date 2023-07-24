<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JurnalUmumPusatDetail;
use Illuminate\Support\Facades\DB;

class JurnalUmumPusatDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete jurnalumumpusatdetail");
        DB::statement("DBCC CHECKIDENT ('jurnalumumpusatdetail', RESEED, 1);");
        
          }
}
