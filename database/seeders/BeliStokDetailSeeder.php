<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BeliStokDetail;

class BeliStokDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        BeliStokDetail::create([
            'belistok_id' => '',            
            'nobukti' => '',
            'stok_id' => '',            
            'conv1' => '',
            'conv2' => '',
            'statusstok' => '',            
            'satuan' => '',
            'qty' => '',
            'hrgsatuan' => '',
            'persentasediscount' => '',
            'nominaldiscount' => '',
            'total' => '',
            'keterangan' => '',
            'gudang_id' => '',            
            'jenisvulkanisir' => '',
            'vulkanisirke' => '',
            'statusban' => '',
            'pindahgudangstok_nobukti' => '',
            'vulkankeawal' => '',
            'statuspindahgudang' => '',
            'modifiedby' => 'ADMIN',
        ]);
    }
}
