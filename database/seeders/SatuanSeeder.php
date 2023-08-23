<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Satuan;
use Illuminate\Support\Facades\DB;

class SatuanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete Satuan");
        DB::statement("DBCC CHECKIDENT ('Satuan', RESEED, 1);");

    }
}
