<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PenerimaanGiroDetail;

class PenerimaanGiroDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PenerimaanGiroDetail::create([
            'penerimaangiro_id' => 1,
            'nobukti' => 'BPGT-M BCA 0001/V/2022',
            'nowarkat' => '',
            'tgljatuhtempo' => '2022/5/20',
            'nominal' => 1021000,
            'coadebet' => '01.03.03.00',
            'coakredit' => '01.03.01.02',
            'keterangan' => 'PENERIMAAN GIRO',
            'bank_id' => 2,
            'pelanggan_id' => 0,
            'invoice_nobukti' => 'INV 0001/IV/2022',
            'bankpelanggan_id' => 0,
            'jenisbiaya' => '',
            'penerimaanpiutang_nobukti' => 'PPT 0001/V/2022',
            'bulanbeban' => '',
            'modifiedby' => 'ADMIN',
        ]);

    }
}
