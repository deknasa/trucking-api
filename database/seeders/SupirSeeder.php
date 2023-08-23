<?php

namespace Database\Seeders;

use App\Models\Supir;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupirSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete Supir");
        DB::statement("DBCC CHECKIDENT ('Supir', RESEED, 1);");


    }
}
