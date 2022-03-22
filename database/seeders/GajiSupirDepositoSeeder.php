<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GajiSupirDeposito;

class GajiSupirDepositoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        GajiSupirDeposito::create([
            'gajisupir_id' => 1,
            'nobukti' => 'RIC 0001/III/2022',
            'nobukti_deposito' => 'DPO 0001/III/2022',
            'tgl' => '2022-03-21',
            'keterangan' => 'DEPOSITO SUPIR',
            'supir_id' => 1,
            'nominal' => 10000,
            'modifiedby' => 'ADMIN',    
        ]);


       
    }
}
