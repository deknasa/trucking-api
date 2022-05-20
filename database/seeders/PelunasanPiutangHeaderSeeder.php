<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PelunasanPiutangHeader;

class PelunasanPiutangHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PelunasanPiutangHeader::create([
            'nobukti' => 'PPT 0001/V/2022',
            'tglbukti' => '2022/5/17',
            'keterangan' => 'PENERIMAAN PIUTANG',
            'bank_id'  => 1,
            'agen_id'  => 2,
            'cabang_id'  => 3,
            'modifiedby' => 'ADMIN',
        ]);

        PelunasanPiutangHeader::create([
            'nobukti' => 'PPT 0002/V/2022',
            'tglbukti' => '2022/5/20',
            'keterangan' => 'PENERIMAAN PIUTANG',
            'bank_id'  => 1,
            'agen_id'  => 2,
            'cabang_id'  => 3,
            'modifiedby' => 'ADMIN',
        ]);

        PelunasanPiutangHeader::create([
            'nobukti' => 'PPT 0003/V/2022',
            'tglbukti' => '2022/5/20',
            'keterangan' => 'PENERIMAAN PIUTANG',
            'bank_id'  => 1,
            'agen_id'  => 2,
            'cabang_id'  => 3,
            'modifiedby' => 'ADMIN',
        ]);
    }
}
