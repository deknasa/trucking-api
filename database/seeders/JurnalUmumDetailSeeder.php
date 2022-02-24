<?php

namespace Database\Seeders;
use App\Models\JurnalUmumDetail;

use Illuminate\Database\Seeder;

class JurnalUmumDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        JurnalUmumDetail::create([
            'jurnalumum_id' => 1,
            'nobukti' => 'KGT 0001/II/2022',
            'tgl' => '2022/2/23',
            'coa' => '09.01.01.01',
            'nominal' => 250000,
            'keterangan' => 'ABSENSI SUPIR', 
            'modifiedby' => 'ADMIN',
            ]);

            JurnalUmumDetail::create([
                'jurnalumum_id' => 1,
                'nobukti' => 'KGT 0001/II/2022',
                'tgl' => '2022/2/23',
                'coa' => '09.01.01.03',
                'nominal' => -250000,
                'keterangan' => 'ABSENSI SUPIR', 
                'modifiedby' => 'ADMIN',
                ]);
    
    }
}
