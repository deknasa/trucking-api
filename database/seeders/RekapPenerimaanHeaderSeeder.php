<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RekapPenerimaanHeader;

class RekapPenerimaanHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        RekapPenerimaanHeader::create([
            'nobukti' => 'RTHT 0001/V/2022',
            'tglbukti' => '2022/5/31',
            'bank_id' => 1,
            'tgltransaksi' => '2022/5/31',
            // 'keterangan' => 'REKAP PENERIMAAN KAS BANK',
            'statusapproval' => 4,
            'userapproval' => '',
            'tglapproval' => '1900/1/1',
            'modifiedby' => 'ADMIN',
        ]);
    }
}
