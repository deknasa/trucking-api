<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ReturBeliStokHeader;

class ReturBeliStokHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ReturBeliStokHeader::create([
            'nobukti' => 'RBT 0001/VI/2022',
            'belistok_nobukti' => 'PBT 0001/VI/2022',
            'tglbukti' => '2022/7/1',
            'supplier_id' => 1,
            'keterangan' => 'RETUR BAUT',
            'modifiedby' => 'ADMIN',


        ]);
    }
}
