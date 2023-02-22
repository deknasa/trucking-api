<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PenerimaanTrucking;

class PenerimaanTruckingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PenerimaanTrucking::create([
            'kodepenerimaan' => 'DPO',
            'keterangan' => 'DEPOSITO SUPIR',
            'coa' => '01.04.02.01',
            'format' => '126',
            'modifiedby' => 'ADMIN',
        ]);
     
        PenerimaanTrucking::create([
            'kodepenerimaan' => 'PJP',
            'keterangan' => 'PENGEMBALIAN PINJAMAN SUPIR',
            'coa' => '01.05.02.02',
            'format' => '125',
            'modifiedby' => 'ADMIN',
        ]);


   
        //
    }
}
