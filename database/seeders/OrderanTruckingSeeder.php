<?php

namespace Database\Seeders;

use App\Models\OrderanTrucking;
use Illuminate\Database\Seeder;

class OrderanTruckingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        OrderanTrucking::create([
            'nobukti' => '0001/III/22',
            'tglbukti' => '2022-03-02',
            'container_id' => 1,
            'agen_id' => 2,
            'jenisorder_id' => 1,
            'pelanggan_id' => 1,
            'tarif_id' => 1,
            'nominal' => 1021000,
            'nojobemkl' => 'PRE1/II/KTR - MD/JKT/22',
            'nocont' => 'SPNU 123456',
            'noseal' => '',
            'nojobemkl2' => '',
            'nocont2' => '',
            'noseal2' => '',
            'statuslangsir' => 80,
            'statusperalihan' => 67,
            'modifiedby' => 'ADMIN'
        ]);
    }
}
