<?php

namespace Database\Seeders;

use App\Models\GajiSupirDetail;
use Illuminate\Database\Seeder;

class GajiSupirDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        GajiSupirDetail::create([
            'gajisupir_id' => 1,
            'nobukti' => 'RIC 0001/III/2022',            
            'nominaldeposito' => 10000,            
            'nominalpengembalianpinjaman' => 10000,            
            'nourut' => 1,            
            'suratpengantar_nobukti' => 'TRP 0001/III/2022',            
            'gajisupir' => 149760,
            'gajikenek' => 15000,
            'gajiritasi' => 0,
            'komisisupir' => 0,            
            'tolsupir' => 0,            
            'voucher' => 0,            
            'novoucher' => '',            
            'modifiedby' => 'ADMIN',    
        ]);

    }
}
