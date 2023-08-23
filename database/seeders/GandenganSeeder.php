<?php

namespace Database\Seeders;

use App\Models\Gandengan;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GandenganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::statement("delete Gandengan");
        DB::statement("DBCC CHECKIDENT ('Gandengan', RESEED, 1);");

           }
}
