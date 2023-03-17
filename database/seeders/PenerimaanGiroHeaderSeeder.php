<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PenerimaanGiroHeader;

class PenerimaanGiroHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PenerimaanGiroHeader::create([
            'nobukti' => 'BPGT-M BCA 0001/V/2022',
            'tglbukti' => '2022/5/20',
            'pelanggan_id' => 0,
            // 'keterangan' => 'PENERIMAAN GIRO',
            'postingdari' => '',
            'diterimadari' => '',
            'tgllunas' => '2022/5/20',
            'cabang_id' => 3,
            'statusapproval' => 4,
            'userapproval' => '',
            'tglapproval' => '1900/1/1',
            'modifiedby' => 'ADMIN',
        ]);
    }
}
