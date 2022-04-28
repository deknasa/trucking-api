<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProsesGajiSupirDetail;

class ProsesGajiSupirDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ProsesGajiSupirDetail::create([
            'nobukti' => 'EBS 0001/III/2022',
            'prosesgajisupir_id' => 1,
            'gajisupir_nobukti' => 'RIC 0001/III/2022',
            'supir_id' => 1,
            'trado_id' => 1,
            'nominal' =>164760,
            'keterangan' => 'PROSES GAJI SUPIR',
            'modifiedby' => 'ADMIN',    
        ]);


 
    }
}
