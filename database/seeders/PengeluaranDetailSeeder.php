<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PengeluaranDetail;
use Illuminate\Support\Facades\DB;

class PengeluaranDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {



        DB::statement("delete pengeluarandetail");
        DB::statement("DBCC CHECKIDENT ('pengeluarandetail', RESEED, 1);");
    }
}
