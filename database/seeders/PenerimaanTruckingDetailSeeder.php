<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PenerimaanTruckingDetail;
use Illuminate\Support\Facades\DB;

class PenerimaanTruckingDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete penerimaantruckingdetail");
        DB::statement("DBCC CHECKIDENT ('penerimaantruckingdetail', RESEED, 1);");

    }
}
