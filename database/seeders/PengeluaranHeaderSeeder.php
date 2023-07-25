<?php

namespace Database\Seeders;

use App\Models\PengeluaranHeader;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PengeluaranHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete pengeluaranheader");
        DB::statement("DBCC CHECKIDENT ('pengeluaranheader', RESEED, 1);");

    }
}
