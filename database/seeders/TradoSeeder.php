<?php

namespace Database\Seeders;

use App\Models\Trado;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TradoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete Trado");
        DB::statement("DBCC CHECKIDENT ('Trado', RESEED, 1);");


    }
}
