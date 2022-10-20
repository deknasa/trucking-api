<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PelunasanPiutangDetail;

class PelunasanPiutangDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PelunasanPiutangDetail::create([
            'nobukti' => 'PPT 0001/V/2022',
            // 'tgl'=> '2022/5/17',
            'pelunasanpiutang_id'=> 1,
            'pelanggan_id'=> 1,
            'agen_id'=> 1,
            'nominal'=> 1021000,
            'piutang_nobukti' => 'EPT 0001/IV/2022',
            'cicilan' => 0,
            'tglcair' => '2022/5/17',
            'keterangan' => 'PELUNASAN PIUTANG',
            'tgljt' => '2022/5/17',
            'penyesuaian'=> 0,
            'coapenyesuaian' => '',
            'invoice_nobukti' => 'INV 0001/IV/2022',
            'keteranganpenyesuaian' => '',
            'modifiedby' => 'ADMIN',
        ]);

        PelunasanPiutangDetail::create([
            'nobukti' => 'PPT 0002/V/2022',
            // 'tgl'=> '2022/5/20',
            'pelunasanpiutang_id'=> 2,
            'pelanggan_id'=> 1,
            'agen_id'=> 1,
            'nominal'=> 0,
            'piutang_nobukti' => 'EPT 0002/IV/2022',
            'cicilan' => 0,
            'tglcair' => '2022/5/20',
            'keterangan' => 'PELUNASAN PIUTANG',
            'tgljt' => '2022/5/20',
            'penyesuaian'=> 300000,
            'coapenyesuaian' => '06.03.01.01',
            'nominallebihbayar'=> 0,
            'coalebihbayar' => '',
            'invoice_nobukti' => 'INE 0001/IV/2022',
            'keteranganpenyesuaian' => '',
            'modifiedby' => 'ADMIN',
        ]);

        PelunasanPiutangDetail::create([
            'nobukti' => 'PPT 0003/V/2022',
            // 'tgl'=> '2022/5/20',
            'pelunasanpiutang_id'=> 3,
            'pelanggan_id'=> 1,
            'agen_id'=> 1,
            'nominal'=> 100000,
            'piutang_nobukti' => 'EPT 0003/IV/2022',
            'cicilan' => 0,
            'tglcair' => '2022/5/20',
            'keterangan' => 'PELUNASAN PIUTANG',
            'tgljt' => '2022/5/20',
            'penyesuaian'=> 0,
            'coapenyesuaian' => '',
            'nominallebihbayar'=> 5000,
            'coalebihbayar' => '06.02.01.01',
            'invoice_nobukti' => '',
            'keteranganpenyesuaian' => '',
            'modifiedby' => 'ADMIN',
        ]);
    }
}
