<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ServiceOutHeader;

class ServiceOutHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ServiceOutHeader::create([
            'nobukti' => 'SOUT 0001/V/2022',
            'tglbukti' => '2022/5/31',
            'trado_id' => 1,
            'tglkeluar' => '2022/5/31',
            'keterangan' => 'Service Opname',
            'modifiedby' => 'ADMIN',
        ]);


    }
}
