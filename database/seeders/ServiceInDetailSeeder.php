<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ServiceInDetail;

class ServiceInDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ServiceInDetail::create([
            'nobukti' => 'SIN 0001/V/2022',
            'servicein_id' => 1,
            'mekanik_id' => 1,
            'keterangan' => 'Service Opname',
            'modifiedby' => 'ADMIN',
        ]);
    }
}
