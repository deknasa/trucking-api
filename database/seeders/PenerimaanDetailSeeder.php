<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PenerimaanDetail;
use Illuminate\Support\Facades\DB;

class PenerimaanDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete penerimaandetail");
        DB::statement("DBCC CHECKIDENT ('penerimaandetail', RESEED, 1);");

    }
}
