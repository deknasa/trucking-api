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

        PenerimaanTrucking::create(['kodepenerimaan' => 'BBM', 'keterangan' => 'HUTANG BBM', 'coadebet' => '01.09.01.06', 'coakredit' => '03.02.02.07', 'coapostingdebet' => '01.01.01.02', 'coapostingkredit' => '01.09.01.06', 'format' => '265', 'modifiedby' => 'ADMIN',]);
        PenerimaanTrucking::create(['kodepenerimaan' => 'PJP', 'keterangan' => 'PENGEMBALIAN PINJAMAN', 'coadebet' => '01.01.01.02', 'coakredit' => '01.05.02.02', 'coapostingdebet' => '01.01.01.02', 'coapostingkredit' => '01.05.02.02', 'format' => '265', 'modifiedby' => 'ADMIN',]);
        PenerimaanTrucking::create(['kodepenerimaan' => 'DPO', 'keterangan' => 'DEPOSITO SUPIR', 'coadebet' => '01.01.01.02', 'coakredit' => '01.04.02.01', 'coapostingdebet' => '01.01.01.02', 'coapostingkredit' => '01.04.02.01', 'format' => '265', 'modifiedby' => 'ADMIN',]);

   
        
    }
}
