<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ritasi;
use Illuminate\Support\Facades\DB;

class RitasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete ritasi");
        DB::statement("DBCC CHECKIDENT ('ritasi', RESEED, 1);");


    }
}
