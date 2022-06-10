<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RekapPenerimaanDetail;

class RekapPenerimaanDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        RekapPenerimaanDetail::create([
            'rekappenerimaan_id' => 1,
            'nobukti' => 'RTHT 0001/V/2022',
            'penerimaan_nobukti' => 'KMT 0001/V/2022',
            'tgltransaksi' => '2022/5/31',
            'nominal' => 600000,
            'keterangan' => 'PENGEMBALIAN KAS GANTUNG',
            'modifiedby' => 'ADMIN',
        ]);
    }
}
