<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Penerima;
use Illuminate\Support\Facades\DB;

class PenerimaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete Penerima");
        DB::statement("DBCC CHECKIDENT ('Penerima', RESEED, 1);");

    }
}
