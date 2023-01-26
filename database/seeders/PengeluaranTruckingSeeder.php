<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PengeluaranTrucking;

class PengeluaranTruckingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
   
        PengeluaranTrucking::create([
            'kodepengeluaran' => 'PJT',
            'keterangan' => 'PINJAMAN SUPIR',
            'coa' => '01.05.02.02',
            'format' => '#PJT #9999#/#R#/#Y',
            'modifiedby' => 'ADMIN',
        ]);

        PengeluaranTrucking::create([
            'kodepengeluaran' => 'BLS',
            'keterangan' => 'BIAYA LAIN SUPIR',
            'coa' => '',
            'format' => '#BLS #9999#/#R#/#Y',
            'modifiedby' => 'ADMIN',
        ]);

    }
}
