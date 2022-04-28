<?php

namespace Database\Seeders;

use App\Models\JurnalUmumDetail;

use Illuminate\Database\Seeder;

class JurnalUmumDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        JurnalUmumDetail::create([
            'jurnalumum_id' => 1,
            'nobukti' => 'KGT 0001/II/2022',
            'tglbukti' => '2022/2/23',
            'coa' => '09.01.01.01',
            'nominal' => 250000,
            'keterangan' => 'ABSENSI SUPIR',
            'modifiedby' => 'ADMIN',
        ]);

        JurnalUmumDetail::create([
            'jurnalumum_id' => 1,
            'nobukti' => 'KGT 0001/II/2022',
            'tglbukti' => '2022/2/23',
            'coa' => '09.01.01.03',
            'nominal' => -250000,
            'keterangan' => 'ABSENSI SUPIR',
            'modifiedby' => 'ADMIN',
        ]);
        // 

        JurnalUmumDetail::create([
            'jurnalumum_id' => 1,
            'nobukti' => 'KGT 0001/II/2022',
            'tglbukti' => '2022/2/23',
            'coa' => '09.01.01.01',
            'nominal' => -250000,
            'keterangan' => 'ABSENSI SUPIR',
            'modifiedby' => 'ADMIN',
        ]);

        JurnalUmumDetail::create([
            'jurnalumum_id' => 1,
            'nobukti' => 'KGT 0001/II/2022',
            'tglbukti' => '2022/2/23',
            'coa' => '09.01.01.03',
            'nominal' => 250000,
            'keterangan' => 'ABSENSI SUPIR',
            'modifiedby' => 'ADMIN',
        ]);
        // 

        JurnalUmumDetail::create([
            'jurnalumum_id' => 2,
            'nobukti' => 'KBT 0001/II/2022',
            'tglbukti' => '2022/2/24',
            'coa' => '01.01.01.02',
            'nominal' => 250000,
            'keterangan' => 'PENGELUARAN',
            'modifiedby' => 'ADMIN',
        ]);

        JurnalUmumDetail::create([
            'jurnalumum_id' => 2,
            'nobukti' => 'KBT 0001/II/2022',
            'tglbukti' => '2022/2/24',
            'coa' => '01.01.02.02',
            'nominal' => -250000,
            'keterangan' => 'PENGELUARAN',
            'modifiedby' => 'ADMIN',
        ]);

        // TGL 22-03-2022

        JurnalUmumDetail::create([
            'jurnalumum_id' => 3,
            'nobukti' => 'KMT 0001/III/2022',
            'tglbukti' => '2022/3/21',
            'coa' => '01.01.01.02',
            'nominal' => 10000,
            'keterangan' => 'PENERIMAAN',
            'modifiedby' => 'ADMIN',
        ]);

        JurnalUmumDetail::create([
            'jurnalumum_id' => 3,
            'nobukti' => 'KMT 0001/III/2022',
            'tglbukti' => '2022/3/21',
            'coa' => '01.04.02.01',
            'nominal' => -10000,
            'keterangan' => 'PENERIMAAN',
            'modifiedby' => 'ADMIN',
        ]);

        JurnalUmumDetail::create([
            'jurnalumum_id' => 4,
            'nobukti' => 'KBT 0001/III/2022',
            'tglbukti' => '2022/3/21',
            'coa' => '01.05.02.02',
            'nominal' => 10000,
            'keterangan' => 'PENGELUARAN',
            'modifiedby' => 'ADMIN',
        ]);

        JurnalUmumDetail::create([
            'jurnalumum_id' => 4,
            'nobukti' => 'KBT 0001/III/2022',
            'tglbukti' => '2022/3/21',
            'coa' => '01.01.01.02',
            'nominal' => -10000,
            'keterangan' => 'PENGELUARAN',
            'modifiedby' => 'ADMIN',
        ]);

        // pengembalian pinjaman
        JurnalUmumDetail::create([
            'jurnalumum_id' => 5,
            'nobukti' => 'KMT 0002/III/2022',
            'tglbukti' => '2022/3/21',
            'coa' => '01.05.02.02',
            'nominal' => -10000,
            'keterangan' => 'PENERIMAAN',
            'modifiedby' => 'ADMIN',
        ]);

        JurnalUmumDetail::create([
            'jurnalumum_id' => 5,
            'nobukti' => 'KMT 0002/III/2022',
            'tglbukti' => '2022/3/21',
            'coa' => '01.01.01.02',
            'nominal' => 10000,
            'keterangan' => 'PENERIMAAN',
            'modifiedby' => 'ADMIN',
        ]);

        JurnalUmumDetail::create([
            'jurnalumum_id' => 6,
            'nobukti' => 'EBS 0001/III/2022',
            'tglbukti' => '2022/3/21',
            'coa' => '07.01.01.01',
            'nominal' => 164760,
            'keterangan' => 'PROSES GAJI SUPIR',
            'modifiedby' => 'ADMIN',
        ]);

        JurnalUmumDetail::create([
            'jurnalumum_id' => 6,
            'nobukti' => 'EBS 0001/III/2022',
            'tglbukti' => '2022/3/21',
            'coa' => '03.02.02.04',
            'nominal' => -164760,
            'keterangan' => 'PROSES GAJI SUPIR',
            'modifiedby' => 'ADMIN',
        ]);

        JurnalUmumDetail::create([
            'jurnalumum_id' => 6,
            'nobukti' => 'EBS 0001/III/2022',
            'tglbukti' => '2022/3/21',
            'coa' => '07.01.01.01',
            'nominal' => 100000,
            'keterangan' => 'PROSES GAJI SUPIR',
            'modifiedby' => 'ADMIN',
        ]);

        JurnalUmumDetail::create([
            'jurnalumum_id' => 6,
            'nobukti' => 'EBS 0001/III/2022',
            'tglbukti' => '2022/3/21',
            'coa' => '03.02.02.04',
            'nominal' => -100000,
            'keterangan' => 'PROSES GAJI SUPIR',
            'modifiedby' => 'ADMIN',
        ]);

        // 08-04-2022 -1

        JurnalUmumDetail::create([
            'jurnalumum_id' => 7,
            'nobukti' => 'KMT 0001/IV/2022',
            'tglbukti' => '2022/4/8',
            'coa' => '01.01.01.02',
            'nominal' => 10000,
            'keterangan' => 'PENERIMAAN',
            'modifiedby' => 'ADMIN',
        ]);

        JurnalUmumDetail::create([
            'jurnalumum_id' => 7,
            'nobukti' => 'KMT 0001/IV/2022',
            'tglbukti' => '2022/4/8',
            'coa' => '01.04.02.01',
            'nominal' => -10000,
            'keterangan' => 'PENERIMAAN',
            'modifiedby' => 'ADMIN',
        ]);

        JurnalUmumDetail::create([
            'jurnalumum_id' => 8,
            'nobukti' => 'KBT 0001/IV/2022',
            'tglbukti' => '2022/4/8',
            'coa' => '01.05.02.02',
            'nominal' => 10000,
            'keterangan' => 'PENGELUARAN',
            'modifiedby' => 'ADMIN',
        ]);

        JurnalUmumDetail::create([
            'jurnalumum_id' => 8,
            'nobukti' => 'KBT 0001/IV/2022',
            'tglbukti' => '2022/4/8',
            'coa' => '01.01.01.02',
            'nominal' => -10000,
            'keterangan' => 'PENGELUARAN',
            'modifiedby' => 'ADMIN',
        ]);

        JurnalUmumDetail::create([
            'jurnalumum_id' => 9,
            'nobukti' => 'PST 0001/IV/2022',
            'tglbukti' => '2022/4/8',
            'coa' => '03.02.02.04',
            'nominal' => 80000,
            'keterangan' => 'PENGELUARAN',
            'modifiedby' => 'ADMIN',
        ]);

        JurnalUmumDetail::create([
            'jurnalumum_id' => 9,
            'nobukti' => 'PST 0001/IV/2022',
            'tglbukti' => '2022/4/8',
            'coa' => '01.01.01.02',
            'nominal' => -80000,
            'keterangan' => 'PENGELUARAN',
            'modifiedby' => 'ADMIN',
        ]);        

        // 22-04-2022

        JurnalUmumDetail::create([
            'jurnalumum_id' => 10,
            'nobukti' => 'EPT 0001/IV/2022',
            'tglbukti' => '2022/4/22',
            'coa' => '01.03.01.02',
            'nominal' => 1021000,
            'keterangan' => 'INVOICE UTAMA',
            'modifiedby' => 'ADMIN',
        ]);

        JurnalUmumDetail::create([
            'jurnalumum_id' => 10,
            'nobukti' => 'EPT 0001/IV/2022',
            'tglbukti' => '2022/4/22',
            'coa' => '06.01.01.02',
            'nominal' => -1021000,
            'keterangan' => 'INVOICE UTAMA',
            'modifiedby' => 'ADMIN',
        ]);    
        
        JurnalUmumDetail::create([
            'jurnalumum_id' => 10,
            'nobukti' => 'EPT 0002/IV/2022',
            'tglbukti' => '2022/4/22',
            'coa' => '01.03.01.02',
            'nominal' => 300000,
            'keterangan' => 'INVOICE EXTRA',
            'modifiedby' => 'ADMIN',
        ]);

        JurnalUmumDetail::create([
            'jurnalumum_id' => 10,
            'nobukti' => 'EPT 0002/IV/2022',
            'tglbukti' => '2022/4/22',
            'coa' => '06.01.01.02',
            'nominal' => -300000,
            'keterangan' => 'INVOICE EXTRA',
            'modifiedby' => 'ADMIN',
        ]); 


        JurnalUmumDetail::create([
            'jurnalumum_id' => 11,
            'nobukti' => 'EPT 0003/IV/2022',
            'tglbukti' => '2022/4/22',
            'coa' => '01.08.01.06',
            'nominal' => 100000,
            'keterangan' => 'PENDAPATAN LAIN',
            'modifiedby' => 'ADMIN',
        ]);

        JurnalUmumDetail::create([
            'jurnalumum_id' => 11,
            'nobukti' => 'EPT 0003/IV/2022',
            'tglbukti' => '2022/4/22',
            'coa' => '06.02.01.01',
            'nominal' => -100000,
            'keterangan' => 'PENDAPATAN LAIN',
            'modifiedby' => 'ADMIN',
        ]); 
    }
}
