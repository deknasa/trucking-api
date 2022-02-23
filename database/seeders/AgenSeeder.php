<?php

namespace Database\Seeders;

use App\Models\Agen;

use Illuminate\Database\Seeder;

class AgenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Agen::create([
            'kodeagen' => 'BSL',
            'namaagen' => 'PT. BERDIKARI SBU LOGISTIC',
            'keterangan' => '',
            'statusaktif' => 1,
            'fnamaperusahaan' => 'PT.BERDIKARI SBU LOGISTIC',
            'alamat' => 'JL.DELI NO.2 MEDAN',
            'notelp' => '061-694253',
            'nohp' => '085100494632',
            'contactperson' => 'ANDI',
            'top' => '30.00',
            'statusapproval' => '3',
            'userapproval' => 'DHENI',
            'tglapproval' => '2017-10-23 15:53:18.000',
            'jenisemkl' => '2',
            'modifiedby' => 'ADMIN',
        ]);
    }
}
