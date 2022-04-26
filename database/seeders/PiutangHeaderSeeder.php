<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PiutangHeader;

class PiutangHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PiutangHeader::create([
            'nobukti' => 'EPT 0001/IV/2022',
            'tglbukti' => '2022/4/22',
            'keterangan' => 'INVOICE UTAMA',
            'postingdari' => 'INVOICE UTAMA',
            'nominal' => 1021000,
            'invoice_nobukti' => 'INV 0001/IV/2022',
            'modifiedby' => 'ADMIN',
        ]);

        PiutangHeader::create([
            'nobukti' => 'EPT 0002/IV/2022',
            'tglbukti' => '2022/4/22',
            'keterangan' => 'INVOICE EXTRA',
            'postingdari' => 'INVOICE EXTRA',
            'nominal' => 300000,
            'invoice_nobukti' => 'INV 0001/IV/2022',
            'modifiedby' => 'ADMIN',
        ]);

        PiutangHeader::create([
            'nobukti' => 'EPT 0003/IV/2022',
            'tglbukti' => '2022/4/22',
            'keterangan' => 'PENDAPATAN LAIN',
            'postingdari' => 'PENDAPATAN LAIN',
            'nominal' => 100000,
            'invoice_nobukti' => '',
            'modifiedby' => 'ADMIN',
        ]);
    }
}
