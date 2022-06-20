<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PoStokHeader;

class PoStokHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PoStokHeader::create([
            'nobukti' => 'POT 0001/VI/2022',
            'tglbukti' => '2022/6/14',
            'supplier_id' => 1,
            'keterangan' => 'PEMBELIAN BAUT',
            'modifiedby' => 'ADMIN',

        ]);
    }
}
