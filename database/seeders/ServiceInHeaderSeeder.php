<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ServiceInHeader;

class ServiceInHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ServiceInHeader::create([
            'nobukti' => 'SIN 0001/V/2022',
            'tglbukti' => '2022/5/31',
            'trado_id' => 1,
            'tglmasuk' => '2022/5/30',
            'keterangan' => 'Service Opname',
            'modifiedby' => 'ADMIN',
        ]);

    }
}
