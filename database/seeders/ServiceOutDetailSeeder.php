<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ServiceOutDetail;


class ServiceOutDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ServiceOutDetail::create([
            'nobukti' => 'SOUT 0001/V/2022',
            'serviceout_id' => 1,
            'servicein_nobukti' => 'SIN 0001/V/2022',
            'keterangan' => 'Service Opname',
            'modifiedby' => 'ADMIN',
        ]);

    }
}
