<?php

namespace Database\Seeders;

use App\Models\Tarif;
use Illuminate\Database\Seeder;

class TarifSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        Tarif::create([
            'tujuan' => 'AMPLAS',
            'container_id' => 1,
            'nominal' => 1021000,
            'statusaktif' => 1,
            'sistemton' => 41,
            'kota_id' => 1,
            'zona_id' => 1,
            'ton' => '',
            'batasberlaku' => '',
            'tglmulaiberlaku' => '',
            'tglakhirberlaku' => '',
            'penyesuaianharga' => '',
            'modifiedby' => '',
        ]);
    }
}
