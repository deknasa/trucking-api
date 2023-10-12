<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\BukaAbsensi;

class BukaAbsensiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete Bukaabsensi");
        DB::statement("DBCC CHECKIDENT ('Bukaabsensi', RESEED, 1);");

    }
}
