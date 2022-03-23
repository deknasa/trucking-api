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
            'tgl' => '2022/2/23',
            'coa' => '09.01.01.01',
            'nominal' => 250000,
            'keterangan' => 'ABSENSI SUPIR', 
            'modifiedby' => 'ADMIN',
            ]);

            JurnalUmumDetail::create([
                'jurnalumum_id' => 1,
                'nobukti' => 'KGT 0001/II/2022',
                'tgl' => '2022/2/23',
                'coa' => '09.01.01.03',
                'nominal' => -250000,
                'keterangan' => 'ABSENSI SUPIR', 
                'modifiedby' => 'ADMIN',
                ]);

        JurnalUmumDetail::create([
            'jurnalumum_id' => 2,
            'nobukti' => 'KBT 0001/II/2022',
            'tgl' => '2022/2/24',
            'coa' => '01.01.01.02',
            'nominal' => 250000,
            'keterangan' => 'PENGELUARAN',
            'modifiedby' => 'ADMIN',
        ]);

        JurnalUmumDetail::create([
            'jurnalumum_id' => 2,
            'nobukti' => 'KBT 0001/II/2022',
            'tgl' => '2022/2/24',
            'coa' => '01.01.02.02',
            'nominal' => -250000,
            'keterangan' => 'PENGELUARAN',
            'modifiedby' => 'ADMIN',
        ]);

        // TGL 22-03-2022

        JurnalUmumDetail::create([
            'jurnalumum_id' => 3,
            'nobukti' => 'KMT 0001/III/2022',
            'tgl' => '2022/3/21',
            'coa' => '01.01.01.02',
            'nominal' => 10000,
            'keterangan' => 'PENERIMAAN',
            'modifiedby' => 'ADMIN',
        ]);

        JurnalUmumDetail::create([
            'jurnalumum_id' => 3,
            'nobukti' => 'KMT 0001/III/2022',
            'tgl' => '2022/3/21',
            'coa' => '01.04.02.01',
            'nominal' => -10000,
            'keterangan' => 'PENERIMAAN',
            'modifiedby' => 'ADMIN',
        ]);

        JurnalUmumDetail::create([
            'jurnalumum_id' => 4,
            'nobukti' => 'KBT 0001/III/2022',
            'tgl' => '2022/3/21',
            'coa' => '01.05.02.02',
            'nominal' => 10000,
            'keterangan' => 'PENGELUARAN',
            'modifiedby' => 'ADMIN',
        ]);

        JurnalUmumDetail::create([
            'jurnalumum_id' => 4,
            'nobukti' => 'KBT 0001/III/2022',
            'tgl' => '2022/3/21',
            'coa' => '01.01.01.02',
            'nominal' => -10000,
            'keterangan' => 'PENGELUARAN',
            'modifiedby' => 'ADMIN',
        ]);

        // pengembalian pinjaman
        JurnalUmumDetail::create([
            'jurnalumum_id' => 5,
            'nobukti' => 'KMT 0002/III/2022',
            'tgl' => '2022/3/21',
            'coa' => '01.05.02.02',
            'nominal' => -10000,
            'keterangan' => 'PENERIMAAN',
            'modifiedby' => 'ADMIN',
        ]);

        JurnalUmumDetail::create([
            'jurnalumum_id' => 5,
            'nobukti' => 'KMT 0002/III/2022',
            'tgl' => '2022/3/21',
            'coa' => '01.01.01.02',
            'nominal' => 10000,
            'keterangan' => 'PENERIMAAN',
            'modifiedby' => 'ADMIN',
        ]);
    }
}
