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
        AbsensiSupirApprovalHeader::create([
            'nobukti' => 'ASA 0001/II/2022',
            'tglbukti' => '2022-02-24',
            'absensisupir_nobukti' => '',
            'keterangan' => '',
            'statusapproval' => 4,
            'tglapproval' => '',
            'userapproval' => '',           
            'modifiedby' => 'ADMIN',
        ]);
    }
}
