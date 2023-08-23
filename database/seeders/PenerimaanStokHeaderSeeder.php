<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PenerimaanStokHeader;
use Illuminate\Support\Facades\DB;

class PenerimaanStokHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete PenerimaanStokHeader");
        DB::statement("DBCC CHECKIDENT ('PenerimaanStokHeader', RESEED, 1);");


    }
}
