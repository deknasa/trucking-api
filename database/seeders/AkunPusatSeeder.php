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
            'statusaccountpayable' => '34',
            'statusneraca' => '36',
            'statuslabarugi' => '38',
            'coamain' => '01.01.01.02',
            'modifiedby' => 'ADMIN',
        ]);
    }
}
