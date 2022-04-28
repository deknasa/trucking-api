<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProsesGajiSupirHeader;

class ProsesGajiSupirHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ProsesGajiSupirHeader::create([
            'nobukti' => 'EBS 0001/III/2022',
            'tglbukti'  => '2022/3/21',
            'keterangan' => 'PROSES GAJI SUPIR',
            'tgldari' => '2022/3/21',            
            'tglsampai' => '2022/3/21',     
            'statusapproval' => 4,
            'userapproval'=> '',
            'tglapproval'   => '',
            'periode'   => '2022/3/21',
            'modifiedby' => 'ADMIN',    
        ]);
    }
}
