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
            // 'pelanggan_id' => 0,
            'invoice_nobukti' => '',
            'bankpelanggan_id' => 0,
            // 'jenisbiaya' => '',
            'pelunasanpiutang_nobukti' => '',
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
            // 'pelanggan_id' => 0,
            'invoice_nobukti' => '',
            'bankpelanggan_id' => 0,
            // 'jenisbiaya' => '',
            'pelunasanpiutang_nobukti' => '',
            'bulanbeban' => '',
            'modifiedby' => 'ADMIN',
        ]);

        PenerimaanDetail::create([
            'penerimaan_id' => 3,
            'nobukti' => 'KMT 0001/IV/2022',
            'nowarkat' => '',
            'tgljatuhtempo' => '2022/4/8',
            'nominal' => 10000,
            'coadebet' => '01.01.01.02',
            'coakredit' => '01.04.02.01',
            'keterangan' => 'DEPOSITO SUPIR',
            'bank_id' => 1,
            // 'pelanggan_id' => 0,
            'invoice_nobukti' => '',
            'bankpelanggan_id' => 0,
            // 'jenisbiaya' => '',
            'pelunasanpiutang_nobukti' => '',
            'bulanbeban' => '',
            'modifiedby' => 'ADMIN',
        ]);

        PenerimaanDetail::create([
            'penerimaan_id' => 4,
            'nobukti' => 'BMT-M BCA 0001/V/2022',
            'nowarkat' => '',
            'tgljatuhtempo' => '2022/5/31',
            'nominal' => 1021000,
            'coadebet' => '01.02.02.01',
            'coakredit' => '01.03.03.00',
            'keterangan' => 'PENERIMAAN BANK',
            'bank_id' => 2,
            // 'pelanggan_id' => 0,
            'invoice_nobukti' => 'INV 0001/IV/2022',
            'bankpelanggan_id' => 0,
            // 'jenisbiaya' => '',
            'pelunasanpiutang_nobukti' => 'BPGT-M BCA 0001/V/2022',
            'bulanbeban' => '',
            'modifiedby' => 'ADMIN',
        ]);

        PenerimaanDetail::create([
            'penerimaan_id' => 5,
            'nobukti' => 'KMT 0001/V/2022',
            'nowarkat' => '',
            'tgljatuhtempo' => '2022/5/31',
            'nominal' => 600000,
            'coadebet' => '01.01.01.02',
            'coakredit' => '01.01.02.02',
            'keterangan' => 'PENGEMBALIAN KAS GANTUNG',
            'bank_id' => 1,
            // 'pelanggan_id' => 0,
            'invoice_nobukti' => '',
            'bankpelanggan_id' => 0,
            // 'jenisbiaya' => '',
            'pelunasanpiutang_nobukti' => '',
            'bulanbeban' => '',
            'modifiedby' => 'ADMIN',
        ]);
    }
}
