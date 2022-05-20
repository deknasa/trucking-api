<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PenerimaanGiroPelunasan;

class PenerimaanGiroPelunasanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PenerimaanGiroPelunasan::create([
            'penerimaangiro_id' => 1,
            'nobukti' => 'BPGT-M BCA 0001/V/2022',
            'penerimaanpiutang_nobukti' => 'PPT 0001/V/2022',
            'tglterima' => '2022/5/20',
            'nominal' => 1021000,
            'modifiedby' => 'ADMIN',
        ]);
    }
}
