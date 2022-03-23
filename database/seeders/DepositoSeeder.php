<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Deposito;

class DepositoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        Deposito::create([
            'nobukti' => 'DPO 0001/III/2022',
            'tgl' => '2022-03-21',
            'keterangan' => 'DEPOSITO SUPIR',
            'supir_id' => 1,
            'bank_id' => 1,
            'nominal' => 10000,
            'coa' => '01.04.02.01',
            'nobuktikasmasuk' => 'KMT 0001/III/2022',
            'tglkasmasuk' => '2022-03-21',
            'modifiedby' => 'ADMIN',    
        ]);
}
}
