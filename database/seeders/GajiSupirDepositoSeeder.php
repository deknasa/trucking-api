<?php

namespace Database\Seeders;

use App\Models\GajiSupirDeposito;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GajiSupirDepositoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete gajisupirdeposito");
        DB::statement("DBCC CHECKIDENT ('gajisupirdeposito', RESEED, 1);");

    }
}
