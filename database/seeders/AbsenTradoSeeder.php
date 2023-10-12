<?php

namespace Database\Seeders;

use App\Models\AbsenTrado;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AbsenTradoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete AbsenTrado");
        DB::statement("DBCC CHECKIDENT ('AbsenTrado',RESEED, 1);");

    }
}
