<?php

namespace Database\Seeders;
use App\Models\SaldoPengeluaranHeader;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Seeder;

class SaldoPengeluaranHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete saldopengeluaranheader");
        DB::statement("DBCC CHECKIDENT ('saldopengeluaranheader', RESEED, 0);");

    }
}
