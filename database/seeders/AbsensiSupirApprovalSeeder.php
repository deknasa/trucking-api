<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AbsensiSupirApproval;

class AbsensiSupirApprovalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        AbsensiSupirApproval::create([
            'tgl' => '2022-02-24',
            'trado_id' => 1,
            'supir_id' => 1,
            'supirserap_id' => 0,
            'statusapproval' => 4,
            'tglapproval' => '',
            'userapproval'  => '',
            'nobukti_absensi' => '',
            'keterangan' => '',
            'statusapprovalpusat' => 4,
            'userapprovalpusat' => '',
            'tglapprovalpusat' => '',
            'keteranganedit' => '',
            'modifiedby' => 'ADMIN',
        ]);
    }
}
