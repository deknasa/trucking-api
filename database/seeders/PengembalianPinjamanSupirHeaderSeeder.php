<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PengembalianPinjamanSupirHeader;


class PengembalianPinjamanSupirHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PengembalianPinjamanSupirHeader::create([
            'nobukti' => 'PJP 0001/III/2022',
            'tgl' => '2021/3/21',
            'keterangan' => 'PENGEMBALIAN PINJAMAN SUPIR',
            'supir_id' => 1,
            'bank_id' => 1,
            'nominal' => 10000,
            'coa' => '01.05.02.02',
            'nobuktikasmasuk' => 'KMT 0002/III/2022',
            'tglkasmasuk' => '2022/3/21',
            'modifiedby' => 'ADMIN',
        ]);
    }
}
