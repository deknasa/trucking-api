<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DeliveryOrderDetail;

class DeliveryOrderDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DeliveryOrderDetail::create([
            'deliveryorder_id' => 1,
            'nobukti' => 'DOT 0001/VIiI/2022',
            'stok_id' => 2,
            'conv1' => 1,
            'conv2' => 1,
            'qty' =>1 ,
            'keterangan' => 'PERBAIKAN RADIATOR',
            'modifiedby' => 'ADMIN',
        ]);

    }
}
