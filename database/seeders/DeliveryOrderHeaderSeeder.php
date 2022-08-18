<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DeliveryOrderHeader;

class DeliveryOrderHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DeliveryOrderHeader::create([
            'nobukti' => 'DOT 0001/VIiI/2022',
            'tglbukti' => '2022/8/15',
            'gudang_id' => 1,            
            'keterangan' => 'PERBAIKAN RADIATOR',
            'modifiedby' => 'ADMIN',
        ]);

 
    }
}
