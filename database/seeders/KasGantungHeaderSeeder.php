<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\KasGantungHeader;

class KasGantungHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        KasGantungHeader::create([
            'nobukti' => 'KGT 0001/II/2022',
            'tgl' => '2022/2/23',
            'penerima_id' => 0,
            'keterangan' => 'ABSENSI SUPIR',
            'bank_id' => 1,
            'nobuktikaskeluar' => 'KBT 0001/II/2022',
            'coakaskeluar' => '01.01.01.02',
            'postingdari' => 'ABSENSI SUPIR',
            'tglkaskeluar' => '2022/2/23',
            'modifiedby' => 'ADMIN',
            ]);
      
    }
}
