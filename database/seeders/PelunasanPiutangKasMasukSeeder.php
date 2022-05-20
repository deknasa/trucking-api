<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PelunasanPiutangKasMasuk;

class PelunasanPiutangKasMasukSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PelunasanPiutangKasMasuk::create([
            'nobukti' => 'PPT 0001/V/2022',
            'pelunasanpiutang_id'=> 1,
            'tgl'=> '2022/5/17',
            'nominal' => 1021000,
            'statuscair' => 0,
            'tglcair' => '2022/5/17',
            'nowarkat' => '',
            'bankwarkat' => '',
            'keterangan' => 'PELUNASAN PIUTANG',
            'coadebet' => '01.01.01.02',
            'coakredit' => '01.03.01.02',
            'postingdari' => '',
            'tgljt' => '2022/5/17',
            'bank_id'  => 1,
            'bankpelanggan_id'  => 1,
            'modifiedby' => 'ADMIN',
        ]);

        PelunasanPiutangKasMasuk::create([
            'nobukti' => 'PPT 0002/V/2022',
            'pelunasanpiutang_id'=> 2,
            'tgl'=> '2022/5/20',
            'nominal' => 0,
            'statuscair' => 0,
            'tglcair' => '2022/5/20',
            'nowarkat' => '',
            'bankwarkat' => '',
            'keterangan' => 'PELUNASAN PIUTANG',
            'coadebet' => '',
            'coakredit' => '',
            'postingdari' => '',
            'tgljt' => '2022/5/20',
            'bank_id'  => 1,
            'bankpelanggan_id'  => 1,
            'modifiedby' => 'ADMIN',
        ]);

          PelunasanPiutangKasMasuk::create([
            'nobukti' => 'PPT 0003/V/2022',
            'pelunasanpiutang_id'=> 3,
            'tgl'=> '2022/5/20',
            'nominal' => 100000,
            'statuscair' => 0,
            'tglcair' => '2022/5/20',
            'nowarkat' => '',
            'bankwarkat' => '',
            'keterangan' => 'PELUNASAN PIUTANG',
            'coadebet' => '01.01.01.02',
            'coakredit' => '01.03.01.02',
            'postingdari' => '',
            'tgljt' => '2022/5/20',
            'bank_id'  => 1,
            'bankpelanggan_id'  => 1,
            'modifiedby' => 'ADMIN',
        ]);


    }
}
