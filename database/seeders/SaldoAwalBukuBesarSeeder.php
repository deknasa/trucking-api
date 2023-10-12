<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SaldoAwalBukubesar;
use Illuminate\Support\Facades\DB;

class SaldoAwalBukuBesarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete saldoawalbukubesar");
        DB::statement("DBCC CHECKIDENT ('saldoawalbukubesar', RESEED, 1);");

    }
}
