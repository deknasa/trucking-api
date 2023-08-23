<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SaldoPendapatanSupir;
use Illuminate\Support\Facades\DB;

class SaldoPendapatanSupirSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete saldopendapatansupir");
        DB::statement("DBCC CHECKIDENT ('saldopendapatansupir', RESEED, 1);");

    }
}
