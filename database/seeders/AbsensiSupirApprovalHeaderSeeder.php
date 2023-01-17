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

        AbsensiSupirapprovalheader::create(['nobukti' => 'ASA 0001/XII/2022', 'tglbukti' => '2022/12/20', 'absensisupir_nobukti' => 'ABS 0001/XII/2022', 'statusapproval' => '4', 'tglapproval' => '1900/1/1', 'userapproval' => '', 'statusformat' => '144', 'pengeluaran_nobukti' => 'KBT 0001/XII/2022', 'coakaskeluar' => '01.01.01.02', 'postingdari' => 'ABSENSI SUPIR APPROVAL', 'tglkaskeluar' => '2022/12/20', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
    }
}
