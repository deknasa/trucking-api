<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PengembalianKasGantungDetail;

class PengembalianKasGantungDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PengembalianKasGantungDetail::create([
            'nobukti' => 'PKGT 0001/V/2022',
            'pengembaliankasgantung_id' => 1,
            'nominal' => 600000,
            'coa' => '01.01.01.02',
            'keterangan' => 'PENGEMBALIAN KAS GANTUNG ',
            'kasgantung_nobukti' => 'KGT 0001/V/2022',
            'modifiedby' => 'ADMIN',
        ]);
    }
}
