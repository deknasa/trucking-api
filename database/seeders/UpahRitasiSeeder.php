<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UpahRitasi;
use Illuminate\Support\Facades\DB;

class UpahRitasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        
        DB::statement("delete UpahRitasi");
        DB::statement("DBCC CHECKIDENT ('UpahRitasi', RESEED, 1);");

    }
}
