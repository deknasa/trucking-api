<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PenerimaanPelunasan;

class PenerimaanPelunasanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PenerimaanPelunasan::create([
            'nobukti' => 'BMT-M BCA 0001/V/2022',
            'penerimaan_id' => 4,
            'penerimaanpiutang_nobukti' => 'BPGT-M BCA 0001/V/2022',
            'tglterima'  => '2022/5/31', 
            'nominal'  => 1021000,
            'modifiedby' => 'ADMIN',
        ]);
    }
}
