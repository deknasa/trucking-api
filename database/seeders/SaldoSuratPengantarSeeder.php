<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SaldoSuratPengantar;
use Illuminate\Support\Facades\DB;

class SaldoSuratPengantarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete saldosuratpengantar");
        DB::statement("DBCC CHECKIDENT ('saldosuratpengantar', RESEED, 1);");

    }
}
