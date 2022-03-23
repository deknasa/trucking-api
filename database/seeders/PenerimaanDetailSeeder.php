<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PenerimaanDetail;

class PenerimaanDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PenerimaanDetail::create([
            'penerimaan_id' => 1,
            'nobukti' => 'KMT 0001/III/2022',
            'nowarkat' => '',
            'tgljatuhtempo' => '2022/3/21',
            'nominal' => 10000,
            'coadebet' => '01.01.01.02',
            'coakredit' => '01.04.02.01',
            'keterangan' => 'DEPOSITO SUPIR',
            'bank_id' => 1,
            'pelanggan_id' => 0,
            'noinvoice' => '',
            'bankpelanggan_id' => 0,
            'jenisbiaya' => '',
            'pelunasan_nobukti' => '',
            'bulanbeban' => '',
            'modifiedby' => 'ADMIN',
        ]);

        PenerimaanDetail::create([
            'penerimaan_id' => 2,
            'nobukti' => 'KMT 0002/III/2022',
            'nowarkat' => '',
            'tgljatuhtempo' => '2022/3/21',
            'nominal' => 10000,
            'coadebet' => '01.01.01.02',
            'coakredit' => '01.05.02.02',
            'keterangan' => 'PENGEMBALIAN PINJAMAN SUPIR',
            'bank_id' => 1,
            'pelanggan_id' => 0,
            'noinvoice' => '',
            'bankpelanggan_id' => 0,
            'jenisbiaya' => '',
            'pelunasan_nobukti' => '',
            'bulanbeban' => '',
            'modifiedby' => 'ADMIN',
        ]);
    }
}
