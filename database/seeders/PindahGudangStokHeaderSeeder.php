<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PindahGudangStokHeader;

class PindahGudangStokHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PindahGudangStokHeader::create([
            'nobukti' => 'PGT 0001/VIII/2022',
            'tglbukti' => '2022/8/15',
            'gudangdari_id' => 1,
            'gudangke_id' => 3,
            'keterangan' => 'PINDAH GUDANG',
            'modifiedby' => 'ADMIN',
        ]);

    }
}
