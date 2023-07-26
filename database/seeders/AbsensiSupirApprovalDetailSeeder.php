<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AbsensiSupirApprovalDetail;
use Illuminate\Support\Facades\DB;

class AbsensiSupirApprovalDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //

        DB::statement("delete AbsensiSupirApprovalDetail");
        DB::statement("DBCC CHECKIDENT ('AbsensiSupirApprovalDetail', RESEED, 1);");

    }
}
