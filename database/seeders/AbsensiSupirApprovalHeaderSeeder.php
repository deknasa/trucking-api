<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AbsensiSupirApprovalHeader;
use Illuminate\Support\Facades\DB;

class AbsensiSupirApprovalHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete AbsensiSupirApprovalHeader");
        DB::statement("DBCC CHECKIDENT ('AbsensiSupirApprovalHeader', RESEED, 1);");

    }
}
