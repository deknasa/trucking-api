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
        AkunPusat::create([
            'coa' => '01.01.01.02',
            'keterangancoa' => 'KAS - FISIK MEDAN',
            'type' => 'KAS',
            'level' => '3',
            'aktif' => '1',
            'parent' => '01.01.01.00',
            'statuscoa' => 63,
            'statusaccountpayable' => '34',
            'statusneraca' => '36',
            'statuslabarugi' => '38',
            'coamain' => '01.01.01.02',
            'modifiedby' => 'ADMIN',
        ]);

        AkunPusat::create([
            'coa' => '09.01.01.01',
            'keterangancoa' => 'ESTIMASI KAS GANTUNG',
            'type' => 'KAS',
            'level' => '3',
            'aktif' => '1',
            'parent' => '09.01.01.00',
            'statuscoa' => 63,
            'statusaccountpayable' => '34',
            'statusneraca' => '36',
            'statuslabarugi' => '38',
            'coamain' => '09.01.01.01',
            'modifiedby' => 'ADMIN',
        ]);        

        AkunPusat::create([
            'coa' => '09.01.01.03',
            'keterangancoa' => 'KAS GANTUNG SEMENTARA',
            'type' => 'KAS',
            'level' => '3',
            'aktif' => '1',
            'parent' => '09.01.01.00',
            'statuscoa' => 63,
            'statusaccountpayable' => '34',
            'statusneraca' => '36',
            'statuslabarugi' => '38',
            'coamain' => '09.01.01.03',
            'modifiedby' => 'ADMIN',
        ]);   
        
        
        AkunPusat::create([
            'coa' => '01.01.02.02',
            'keterangancoa' => 'KAS - GANTUNG MEDAN',
            'type' => 'KAS',
            'level' => '3',
            'aktif' => '1',
            'parent' => '01.01.02.00',
            'statuscoa' => 63,
            'statusaccountpayable' => '34',
            'statusneraca' => '36',
            'statuslabarugi' => '38',
            'coamain' => '01.01.02.02',
            'modifiedby' => 'ADMIN',
        ]);

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

 AkunPusat::create([
            'coa' => '07.01.01.01',
            'keterangancoa' => 'B.LAP - BORONGAN',
            'type' => 'KAS',
            'level' => '3',
            'aktif' => '1',
            'parent' => '07.01.01.00',
            'statuscoa' => 63,
            'statusaccountpayable' => '34',
            'statusneraca' => '36',
            'statuslabarugi' => '38',
            'coamain' => '07.01.01.01',
            'modifiedby' => 'ADMIN',
        ]);        

        AkunPusat::create([
            'coa' => '03.02.02.04',
            'keterangancoa' => 'HUTANG PREDIKSI',
            'type' => 'KAS',
            'level' => '3',
            'aktif' => '1',
            'parent' => '03.02.02.00',
            'statuscoa' => 63,
            'statusaccountpayable' => '34',
            'statusneraca' => '36',
            'statuslabarugi' => '38',
            'coamain' => '03.02.02.04',
            'modifiedby' => 'ADMIN',
        ]);    

        // 22-04-2022
        AkunPusat::create([
            'coa' => '01.03.01.02',
            'keterangancoa' => 'PIUTANG USAHA - DIV.EMKL MEDAN',
            'type' => 'KAS',
            'level' => '3',
            'aktif' => '1',
            'parent' => '01.03.01.00',
            'statuscoa' => 63,
            'statusaccountpayable' => '34',
            'statusneraca' => '36',
            'statuslabarugi' => '38',
            'coamain' => '01.03.01.02',
            'modifiedby' => 'ADMIN',
        ]);        

        AkunPusat::create([
            'coa' => '06.01.01.02',
            'keterangancoa' => 'PENDAPATAN - USAHA MEDAN',
            'type' => 'KAS',
            'level' => '3',
            'aktif' => '1',
            'parent' => '06.01.01.00',
            'statuscoa' => 63,
            'statusaccountpayable' => '34',
            'statusneraca' => '36',
            'statuslabarugi' => '38',
            'coamain' => '06.01.01.02',
            'modifiedby' => 'ADMIN',
        ]);     
        


        AkunPusat::create([
            'coa' => '06.02.01.01',
            'keterangancoa' => 'PENDAPATAN - LAIN',
            'type' => 'KAS',
            'level' => '3',
            'aktif' => '1',
            'parent' => '06.02.01.00',
            'statuscoa' => 63,
            'statusaccountpayable' => '34',
            'statusneraca' => '36',
            'statuslabarugi' => '38',
            'coamain' => '06.02.01.01',
            'modifiedby' => 'ADMIN',
        ]);     

        AkunPusat::create([
            'coa' => '01.08.01.06',
            'keterangancoa' => 'PIUTANG - LAIN',
            'type' => 'KAS',
            'level' => '3',
            'aktif' => '1',
            'parent' => '01.08.01.00',
            'statuscoa' => 63,
            'statusaccountpayable' => '34',
            'statusneraca' => '36',
            'statuslabarugi' => '38',
            'coamain' => '01.08.01.06',
            'modifiedby' => 'ADMIN',
        ]);  

        AkunPusat::create([
            'coa' => '06.03.01.01',
            'keterangancoa' => 'POTONGAN PENDAPATAN USAHA',
            'type' => 'KAS',
            'level' => '3',
            'aktif' => '1',
            'parent' => '01.08.01.00',
            'statuscoa' => 63,
            'statusaccountpayable' => '34',
            'statusneraca' => '36',
            'statuslabarugi' => '38',
            'coamain' => '06.03.01.01',
            'modifiedby' => 'ADMIN',
        ]); 

        AkunPusat::create([
            'coa' => '01.03.03.00',
            'keterangancoa' => 'PIUTANG GIRO',
            'type' => 'KAS',
            'level' => '3',
            'aktif' => '1',
            'parent' => '01.03.03.00',
            'statuscoa' => 63,
            'statusaccountpayable' => '34',
            'statusneraca' => '36',
            'statuslabarugi' => '38',
            'coamain' => '01.03.03.00',
            'modifiedby' => 'ADMIN',
        ]); 

        AkunPusat::create([
            'coa' => '01.02.02.01',
            'keterangancoa' => 'BCA 3490977545',
            'type' => 'BANK',
            'level' => '3',
            'aktif' => '1',
            'parent' => '01.02.02.00',
            'statuscoa' => 63,
            'statusaccountpayable' => '34',
            'statusneraca' => '36',
            'statuslabarugi' => '38',
            'coamain' => '01.02.02.01',
            'modifiedby' => 'ADMIN',
        ]); 

    }
}
