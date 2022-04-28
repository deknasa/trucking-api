<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PengeluaranTruckingHeader;

class PengeluaranTruckingHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PengeluaranTruckingHeader::create([
            'nobukti' => 'PJT 0001/III/2022',
            'tglbukti' => '2022-03-21',
            'pengeluarantrucking_id' => 1,
            'keterangan' => 'PINJAMAN SUPIR',
            'bank_id' => 1,
            'coa' => '01.05.02.02',
            'pengeluaran_nobukti' => 'KBT 0001/III/2022',
            'pengeluaran_tgl' => '2022-03-21',
            'statusposting' => 83,
            'proses_nobukti' => '',
            'modifiedby' => 'ADMIN',

        ]);

        PengeluaranTruckingHeader::create([
            'nobukti' => 'BLS 0001/III/2022',
            'tglbukti' => '2022-03-21',
            'pengeluarantrucking_id' => 2,
            'keterangan' => 'TAMBAHAN BIAYA SOLAR',
            'bank_id' => 0,
            'coa' => '',
            'pengeluaran_nobukti' => '',
            'pengeluaran_tgl' => '1900/1/1',
            'statusposting' => 84,
            'proses_nobukti' => 'EBS 0001/III/2022',
            'modifiedby' => 'ADMIN',

        ]);

        PengeluaranTruckingHeader::create([
            'nobukti' => 'PJT 0001/IV/2022',
            'tglbukti' => '2022-04-08',
            'pengeluarantrucking_id' => 1,
            'keterangan' => 'PINJAMAN SUPIR',
            'bank_id' => 1,
            'coa' => '01.05.02.02',
            'pengeluaran_nobukti' => 'KBT 0001/IV/2022',
            'pengeluaran_tgl' => '2022-04-08',
            'statusposting' => 83,
            'proses_nobukti' => '',
            'modifiedby' => 'ADMIN',

        ]);
    }
}
