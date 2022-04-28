<?php

namespace Database\Seeders;

use App\Models\AbsensiSupirHeader;
use Illuminate\Database\Seeder;

class AbsensiSupirHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        AbsensiSupirHeader::create([
            'nobukti' => 'ABS 0001/II/2022',
            'tglbukti' => '2022/2/23',
            'keterangan' => 'ABSENSI SUPIR TGL 23-02-2022',
            'kasgantung_nobukti' => 'KGT 0001/II/2022',
            'nominal' => '250000',
            'modifiedby' => 'ADMIN',
        ]);
    }
}
