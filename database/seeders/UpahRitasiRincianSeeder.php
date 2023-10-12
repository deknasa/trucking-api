<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UpahRitasiRincian;
use Illuminate\Support\Facades\DB;

class UpahRitasiRincianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete UpahRitasiRincian");
        DB::statement("DBCC CHECKIDENT ('UpahRitasiRincian', RESEED, 1);");

    }
}
