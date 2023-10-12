<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PenerimaanTrucking;
use Illuminate\Support\Facades\DB;

class PenerimaanTruckingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete PenerimaanTrucking");
        DB::statement("DBCC CHECKIDENT ('PenerimaanTrucking', RESEED, 1);");

        penerimaantrucking::create(['kodepenerimaan' => 'BBM', 'keterangan' => 'HUTANG BBM', 'coadebet' => '01.09.01.06', 'coakredit' => '03.02.02.07', 'coapostingdebet' => '01.01.01.03', 'coapostingkredit' => '01.09.01.06', 'format' => '265', 'modifiedby' => 'ADMIN', 'info' => '',]);
        penerimaantrucking::create(['kodepenerimaan' => 'PJP', 'keterangan' => 'PENGEMBALIAN PINJAMAN', 'coadebet' => '01.01.01.03', 'coakredit' => '01.05.03.02', 'coapostingdebet' => '01.01.01.03', 'coapostingkredit' => '01.05.03.02', 'format' => '126', 'modifiedby' => 'ADMIN', 'info' => '',]);
        penerimaantrucking::create(['kodepenerimaan' => 'DPO', 'keterangan' => 'DEPOSITO SUPIR', 'coadebet' => '01.01.01.03', 'coakredit' => '01.04.03.01', 'coapostingdebet' => '01.01.01.03', 'coapostingkredit' => '01.04.03.01', 'format' => '125', 'modifiedby' => 'ADMIN', 'info' => '',]);
        penerimaantrucking::create(['kodepenerimaan' => 'PJPK', 'keterangan' => 'PENGEMBALIAN PINJAMAN KARYAWAN', 'coadebet' => '01.01.01.03', 'coakredit' => '01.05.03.01', 'coapostingdebet' => '01.01.01.03', 'coapostingkredit' => '01.05.03.01', 'format' => '370', 'modifiedby' => 'ADMIN', 'info' => '',]);
        penerimaantrucking::create(['kodepenerimaan' => 'PBT', 'keterangan' => 'PENGEMBALIAN TITIPAN EMKL', 'coadebet' => '01.01.01.03', 'coakredit' => '01.08.01.06', 'coapostingdebet' => '01.01.01.03', 'coapostingkredit' => '01.08.01.06', 'format' => '410', 'modifiedby' => 'ADMIN', 'info' => '',]);
    }
}
