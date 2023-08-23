<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SaldoHutangPrediksi;
use Illuminate\Support\Facades\DB;

class SaldoHutangPrediksiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete saldohutangprediksi");
        DB::statement("DBCC CHECKIDENT ('saldohutangprediksi', RESEED, 1);");

    }
}
