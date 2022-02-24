<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BeliVulkanStokDetail;
class BeliVulkanStokDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        BeliVulkanStokDetail::create([
            'belivulkanstok_id' => '',
            'nobukti' => '',
            'stok_id' => '',
            'conv1' => '',
            'conv2' => '',
            'statusstok' => '',    
            'satuan' => '',
            'qty' => '',
            'hrgsat' => '',
            'persentasediscount' => '',
            'nominaldiscount' => '',
            'total' => '',
            'keterangan' => '',
            'gudang_id' => '',
            'jenisvulkan' => '',
            'vulkanisirke' => '',            
            'statusban' => '',
            'pindahgudangstok_nobukti' => '',
            'vulkankeawal' => '',            
            'statuspindahgudang' => '',  
            'modifiedby' => 'ADMIN',
        ]);
    }
}
