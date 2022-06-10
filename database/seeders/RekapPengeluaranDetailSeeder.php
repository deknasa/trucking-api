<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RekapPengeluaranDetail;

class RekapPengeluaranDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        RekapPengeluaranDetail::create([
            'rekappengeluaran_id' => 1,
            'nobukti' => 'RKHT 0001/V/2022',
            'pengeluaran_nobukti' => 'KBT 0001/V/2022',
            'tgltransaksi' => '2022/5/31',
            'nominal' => 600000,
            'keterangan' => 'KAS GANTUNG KO ASAN UNTUK BELI KEBUTUHAN SEMBAHYANG',
            'modifiedby' => 'ADMIN',
        ]);
    }
}
