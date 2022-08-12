<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\BeliStokHeader;
class BeliStokHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        BeliStokHeader::create([
            'nobukti' => 'PBT 0001/VII/2022',
            'tglbukti' => '2022/7/1',
            'postok_nobukti' => 'POT 0001/VI/2022',
            'supplier_id' => 1,            
            'keterangan' => 'PEMBELIAN BAUT',
            'nobon' => '',
            'hutang_nobukti' => '',
            'modifiedby' => 'ADMIN',
        ]);
    }
}
