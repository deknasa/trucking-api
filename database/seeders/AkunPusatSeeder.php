<?php

namespace Database\Seeders;

use App\Models\AkunPusat;
use Illuminate\Database\Seeder;

class AkunPusatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // AkunPusat::create([
        //     'coa' => '01.01.01.02',
        //     'keterangancoa' => 'KAS - FISIK MEDAN',
        //     'type' => 'KAS',
        //     'level' => '3',
        //     'aktif' => '1',
        //     'parent' => '01.01.01.00',
        //     'statuscoa' => 63,
        //     'statusaccountpayable' => '34',
        //     'statusneraca' => '36',
        //     'statuslabarugi' => '38',
        //     'coamain' => '01.01.01.02',
        //     'modifiedby' => 'ADMIN',
        // ]);

        // AkunPusat::create([
        //     'coa' => '09.01.01.01',
        //     'keterangancoa' => 'ESTIMASI KAS GANTUNG',
        //     'type' => 'KAS',
        //     'level' => '3',
        //     'aktif' => '1',
        //     'parent' => '09.01.01.00',
        //     'statuscoa' => 63,
        //     'statusaccountpayable' => '34',
        //     'statusneraca' => '36',
        //     'statuslabarugi' => '38',
        //     'coamain' => '09.01.01.01',
        //     'modifiedby' => 'ADMIN',
        // ]);        

        // AkunPusat::create([
        //     'coa' => '09.01.01.03',
        //     'keterangancoa' => 'KAS GANTUNG SEMENTARA',
        //     'type' => 'KAS',
        //     'level' => '3',
        //     'aktif' => '1',
        //     'parent' => '09.01.01.00',
        //     'statuscoa' => 63,
        //     'statusaccountpayable' => '34',
        //     'statusneraca' => '36',
        //     'statuslabarugi' => '38',
        //     'coamain' => '09.01.01.03',
        //     'modifiedby' => 'ADMIN',
        // ]);   
        
        
        // AkunPusat::create([
        //     'coa' => '01.01.02.02',
        //     'keterangancoa' => 'KAS - GANTUNG MEDAN',
        //     'type' => 'KAS',
        //     'level' => '3',
        //     'aktif' => '1',
        //     'parent' => '01.01.02.00',
        //     'statuscoa' => 63,
        //     'statusaccountpayable' => '34',
        //     'statusneraca' => '36',
        //     'statuslabarugi' => '38',
        //     'coamain' => '01.01.02.02',
        //     'modifiedby' => 'ADMIN',
        // ]);

        // tgl 22-03-2022
        AkunPusat::create([
            'coa' => '01.04.02.01',
            'keterangancoa' => 'DEPOSITO SUPIR - MEDAN',
            'type' => 'KAS',
            'level' => '3',
            'aktif' => '1',
            'parent' => '01.04.02.00',
            'statuscoa' => 63,
            'statusaccountpayable' => '34',
            'statusneraca' => '36',
            'statuslabarugi' => '38',
            'coamain' => '01.04.02.01',
            'modifiedby' => 'ADMIN',
        ]);

        AkunPusat::create([
            'coa' => '01.05.02.02',
            'keterangancoa' => 'PINJAMAN SUPIR - MEDAN',
            'type' => 'KAS',
            'level' => '3',
            'aktif' => '1',
            'parent' => '01.05.02.00',
            'statuscoa' => 63,
            'statusaccountpayable' => '34',
            'statusneraca' => '36',
            'statuslabarugi' => '38',
            'coamain' => '01.05.02.02',
            'modifiedby' => 'ADMIN',
        ]);


        
    }
}
