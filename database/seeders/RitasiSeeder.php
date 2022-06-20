<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ritasi;

class RitasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Ritasi::create([
            'nobukti' => 'RTT 0001/VI/2022',
            'tglbukti' => '2022/6/14',
            'statusritasi' => 89,
            'suratpengantar_nobukti' => '',
            'supir_id' => 1,
            'trado_id' => 1,
            'jarak' => 30,
            'gaji' => 149760,
            'dari_id' => 1,
            'sampai_id' => 3,
            'modifiedby' => 'ADMIN',

        ]);
    
    }
}
