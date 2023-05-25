<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PengembalianKasGantungHeader;

class PengembalianKasGantungHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        PengembalianKasGantungHeader::create([
            'nobukti' => 'PKGT 0001/V/2022',
            'tglbukti' => '2022/5/31',
            'keterangan' => 'PENGEMBALIAN KAS GANTUNG',
            'bank_id' => 1,
            'tgldari' => '',
            'tglsampai' => '',
            'penerimaan_nobukti' => 'KMT 0001/V/2022',
            'coakasmasuk' => '01.01.01.02',
            'postingdari' => '',
            'tglkasmasuk' => '2022/5/31',
            'modifiedby' => 'ADMIN',
        ]);
    }
}
