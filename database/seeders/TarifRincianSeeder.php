<?php

namespace Database\Seeders;

use App\Models\TarifRincian;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TarifRincianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete TarifRincian");
        DB::statement("DBCC CHECKIDENT ('TarifRincian', RESEED, 1);");


    }
}
