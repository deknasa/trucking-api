<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PengeluaranDetail;

class PengeluaranDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PengeluaranDetail::create([
            'pengeluaran_id' => 1,
            'nobukti' => 'KBT 0001/II/2022',
            'alatbayar_id' => 1,
            'nowarkat' => '',
            'tgljatuhtempo' => '2022-02-24',
            'nominal' => 250000,
            'coadebet' => '01.01.01.02',
            'coakredit' => '01.01.02.02',
            'keterangan' => 'PROSES ABSENSI SUPIR',
            'bulanbeban' => '2022-02-23',
            'modifiedby' => 'ADMIN',
        ]);

        // pinjaman
        PengeluaranDetail::create([
            'pengeluaran_id' => 2,
            'nobukti' => 'KBT 0001/III/2022',
            'alatbayar_id' => 1,
            'nowarkat' => '',
            'tgljatuhtempo' => '2022-02-24',
            'nominal' => 10000,
            'coadebet' => '01.05.02.02',
            'coakredit' => '01.01.02.02',
            'keterangan' => 'PINJAMAN SUPIR',
            'bulanbeban' => '2022-03-21',
            'modifiedby' => 'ADMIN',
        ]);

        PengeluaranDetail::create([
            'pengeluaran_id' => 3,
            'nobukti' => 'KBT 0001/IV/2022',
            'alatbayar_id' => 1,
            'nowarkat' => '',
            'tgljatuhtempo' => '2022-04-8',
            'nominal' => 15000,
            'coadebet' => '01.05.02.02',
            'coakredit' => '01.01.02.02',
            'keterangan' => 'PINJAMAN SUPIR',
            'bulanbeban' => '2022-04-08',
            'modifiedby' => 'ADMIN',
        ]);
    }
}
