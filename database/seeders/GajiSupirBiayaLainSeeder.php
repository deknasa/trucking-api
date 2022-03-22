<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GajiSupirBiayaLain;

class GajiSupirBiayaLainSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        GajiSupirBiayaLain::create([
            'gajisupir_id' => 1,
            'nobukti' => 'RIC 0001/III/2022',
            'keteranganbiaya' => 'TAMBAHAN BIAYA SOLAR',
            'nominal' => 100000,
            'modifiedby' => 'ADMIN',    
        ]);
    }
}
