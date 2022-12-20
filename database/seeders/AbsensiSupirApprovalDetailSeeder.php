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

        AbsensiSupirapprovaldetail::create(['nobukti' => 'ASA 0001/XII/2022', 'absensisupirapproval_id' => '1', 'trado_id' => '1', 'supir_id' => '1', 'supirserap_id' => '0', 'modifiedby' => 'ADMIN',]);
    }
}
