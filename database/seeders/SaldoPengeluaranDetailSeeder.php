<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SaldoPengeluaranDetail;
use Illuminate\Support\Facades\DB;


class SaldoPengeluaranDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete saldopengeluarandetail");
        DB::statement("DBCC CHECKIDENT ('saldopengeluarandetail', RESEED, 1);");


    }
}
