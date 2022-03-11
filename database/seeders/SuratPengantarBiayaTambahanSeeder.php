<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SuratPengantarBiayaTambahan;

class SuratPengantarBiayaTambahanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        SuratPengantarBiayaTambahan::create([
            'suratpengantar_id' =>1,
            'keteranganbiaya' => 'TAMBAHAN SOLAR',
            'nominal' => 100000,
            'modifiedby' => 'ADMIN' ,
    
        ]);
    }
}
