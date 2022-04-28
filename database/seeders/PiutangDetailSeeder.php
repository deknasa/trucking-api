<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PiutangDetail;

class PiutangDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PiutangDetail::create([
            'nobukti' => 'EPT 0001/IV/2022',
            'piutang_id' => 1,
            'keterangan' => 'INVOICE UTAMA',
            'nominal' => 1021000,
            'invoice_nobukti' => 'INV 0001/IV/2022',
            'modifiedby' => 'ADMIN',
        ]);

        PiutangDetail::create([
            'nobukti' => 'EPT 0002/IV/2022',
            'piutang_id' => 2,
            'keterangan' => 'INVOICE EXTRA',
            'nominal' => 300000,
            'invoice_nobukti' => 'INE 0001/IV/2022',
            'modifiedby' => 'ADMIN',
            ]);

            PiutangDetail::create([
                'nobukti' => 'EPT 0003/IV/2022',
                'piutang_id' => 3,
                'keterangan' => 'PENDAPATAN LAIN',
                'nominal' => 100000,
                'invoice_nobukti' => '',
                'modifiedby' => 'ADMIN',
                ]);
    }
}
