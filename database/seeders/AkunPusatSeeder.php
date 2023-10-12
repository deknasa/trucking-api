<?php

namespace Database\Seeders;

use App\Models\AkunPusat;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AkunPusatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        // Akunpusat::truncate();
        DB::statement("delete Akunpusat");
        DB::statement("DBCC CHECKIDENT ('Akunpusat', RESEED, 1);");



    }
}
