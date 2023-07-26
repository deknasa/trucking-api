<?php

namespace Database\Seeders;

use App\Models\OrderanTrucking;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderanTruckingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete orderantrucking");
        DB::statement("DBCC CHECKIDENT ('orderantrucking', RESEED, 1);");



    }
}
