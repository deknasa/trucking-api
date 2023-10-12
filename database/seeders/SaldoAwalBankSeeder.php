<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use App\Models\SaldoAwalBank;
use Illuminate\Support\Facades\DB;

class SaldoAwalBankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete saldoawalbank");
        DB::statement("DBCC CHECKIDENT ('saldoawalbank', RESEED, 1);");

    }
}
