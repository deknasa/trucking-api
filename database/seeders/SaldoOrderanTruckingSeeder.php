<?php

namespace Database\Seeders;
use App\Models\SaldoOrderanTrucking;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Seeder;

class SaldoOrderanTruckingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete saldoorderantrucking");
        DB::statement("DBCC CHECKIDENT ('saldoorderantrucking', RESEED, 1);");

    }
}
