<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PiutangDetail;
use Illuminate\Support\Facades\DB;

class PiutangDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete piutangdetail");
        DB::statement("DBCC CHECKIDENT ('piutangdetail', RESEED, 1);");

      
    }
}
