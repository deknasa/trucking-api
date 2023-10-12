<?php

namespace Database\Seeders;

use App\Models\Subkelompok;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubKelompokSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete Subkelompok");
        DB::statement("DBCC CHECKIDENT ('Subkelompok', RESEED, 1);");

            }
}
