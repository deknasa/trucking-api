<?php

namespace Database\Seeders;

use App\Models\AbsenTrado;
use Illuminate\Database\Seeder;

class AbsenTradoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        AbsenTrado::create([
            'kodeabsen' => 'I',
            'keterangan' => 'INAP',
            'statusaktif' => '1',
            'modifiedby' => 'ADMIN',
        ]);

        AbsenTrado::create([
            'kodeabsen' => 'G',
            'keterangan' => 'GANTUNG',
            'statusaktif' => '1',
            'modifiedby' => 'ADMIN',
        ]);        

        AbsenTrado::create([
            'kodeabsen' => 'TS',
            'keterangan' => 'TIDAK ADA SUPIR',
            'statusaktif' => '1',
            'modifiedby' => 'ADMIN',
        ]);                
    }
}
