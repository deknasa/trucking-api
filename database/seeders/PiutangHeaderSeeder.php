<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PiutangHeader;
use Illuminate\Support\Facades\DB;

class PiutangHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete piutangheader");
        DB::statement("DBCC CHECKIDENT ('piutangheader', RESEED, 1);");

           }
}
