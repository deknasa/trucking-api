<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StokPersediaan;
use Illuminate\Support\Facades\DB;

class StokPersediaanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete StokPersediaan");
        DB::statement("DBCC CHECKIDENT ('StokPersediaan', RESEED, 1);");

    }
}
