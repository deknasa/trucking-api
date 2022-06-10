<?php

namespace Database\Seeders;
use App\Models\KasGantungDetail;
use Illuminate\Database\Seeder;

class KasGantungDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        KasGantungDetail::create([
            'kasgantung_id' => 1,
            'nobukti' => 'KGT 0001/II/2022',
            'nominal' => 250000,
            'coa' => '09.01.01.03',
            'keterangan' => 'ABSENSI SUPIR',
            'modifiedby' => 'ADMIN',
            ]);

            KasGantungDetail::create([
                'kasgantung_id' => 2,
                'nobukti' => 'KGT 0001/V/2022',
                'nominal' => 600000,
                'coa' => '',
                'keterangan' => 'KAS GANTUNG KO ASAN UNTUK BELI KEBUTUHAN SEMBAHYANG',
                'modifiedby' => 'ADMIN',
                ]);
    }
}
