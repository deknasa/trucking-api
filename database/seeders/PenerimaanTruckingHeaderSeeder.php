<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PenerimaanTruckingHeader;

class PenerimaanTruckingHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PenerimaanTruckingHeader::create([
            'nobukti' => 'DPO 0001/III/2022',
            'tglbukti' => '2022-03-21',
            'penerimaantrucking_id' => 1,
            'keterangan' => 'DEPOSITO SUPIR',
            'bank_id' => 1,
            'coa' => '01.04.02.01',
            'penerimaan_nobukti' => 'KMT 0001/III/2022',
            'penerimaan_tgl' => '2022-03-21',
            'proses_nobukti' => 'EBS 0001/III/2022',            
            'modifiedby' => 'ADMIN',

        ]);

        PenerimaanTruckingHeader::create([
            'nobukti' => 'PJP 0001/III/2022',
            'tglbukti' => '2021/3/21',
            'penerimaantrucking_id' => 2,
            'keterangan' => 'PENGEMBALIAN PINJAMAN SUPIR',
            'bank_id' => 1,
            'coa' => '01.05.02.02',
            'penerimaan_nobukti' => 'KMT 0002/III/2022',
            'penerimaan_tgl' => '2022/3/21',
            'proses_nobukti' => 'EBS 0001/III/2022',            
            'modifiedby' => 'ADMIN',
        ]);

        PenerimaanTruckingHeader::create([
            'nobukti' => 'DPO 0001/IV/2022',
            'tglbukti' => '2022-04-08',
            'penerimaantrucking_id' => 1,
            'keterangan' => 'DEPOSITO SUPIR',
            'bank_id' => 1,
            'coa' => '01.04.02.01',
            'penerimaan_nobukti' => 'KMT 0001/IV/2022',
            'penerimaan_tgl' => '2022-04-08',
            'proses_nobukti' => 'PST 0001/III/2022',              
            'modifiedby' => 'ADMIN',

        ]);

       
    }
}
