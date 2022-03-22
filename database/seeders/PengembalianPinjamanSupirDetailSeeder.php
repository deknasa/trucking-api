<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PengembalianPinjamanSupirDetail;

class PengembalianPinjamanSupirDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PengembalianPinjamanSupirDetail::create([
            'pengembalianpinjaman_id' => 1,
            'nobukti' => 'PJP 0001/III/2022',
            'tgl' => '2022/3/21',
            'keterangan' =>'PENGEMBALIAN PINJAMAN SUPIR',
            'nobukti_pinjaman' => 'PJT 0001/III/2022',
            'supir_id' => 1,
            'nominal' => 10000,
            'modifiedby' => 'ADMIN',
        ]);
    }
}
