<?php

namespace Database\Seeders;
use App\Models\BeliVulkanStokHeader;
use Illuminate\Database\Seeder;

class BeliVulkanStokHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        BeliVulkanStokHeader::create([
            'nobukti' => '',
            'tgl' => '',
            'supplier_id' => '',   
            'persentasediscount' => '',
            'nominaldiscount' => '',
            'persentaseppn' => '',
            'nominalppn' => '',
            'total' => '',
            'keterangan' => '',
            'tgljatuhtempo' => '',
            'nobon' => '',
            'statusedit' => '',            
            'hutang_nobukti' => '',
            'modifiedby' => 'ADMIN',
        ]);
    }
}
