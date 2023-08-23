<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PenerimaanTruckingHeader;
use Illuminate\Support\Facades\DB;

class PenerimaanTruckingHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete penerimaantruckingheader");
        DB::statement("DBCC CHECKIDENT ('penerimaantruckingheader', RESEED, 1);");

    }
}
