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

        AbsensiSupirApprovalHeader::create([ 'nobukti' => 'ASA 0001/II/2022', 'tglbukti' => '2022/2/24', 'absensisupir_nobukti' => '', 'keterangan' => '', 'statusapproval' => '4', 'tglapproval' => '1900/1/1', 'userapproval' => '', 'statusformat' => '144', 'modifiedby' => 'ADMIN',]);
    }
}
