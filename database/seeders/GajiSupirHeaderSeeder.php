<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GajiSupirHeader;

class GajiSupirHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        GajiSupirHeader::create([
            'nobukti' => 'RIC 0001/III/2022',            
            'tglbukti' => '2022/3/21',            
            'supir_id' => 1,            
            'nominal' => 164760,            
            'keterangan' => 'upah supir',            
            'tgldari' => '2022/3/21',            
            'tglsampai' => '2022/3/21',            
            'total' => 164760,            
            'uangjalan' => 0,            
            'bbm' => '',            
            'potonganpinjaman' => 0,            
            'deposito' => 0,            
            'potonganpinjamansemua' => 0,            
            'komisisupir' => 0,            
            'tolsupir' => 0,            
            'voucher' => 0,            
            'uangmakanharian' => 0,            
            'pinjamanpribadi' => 0,            
            'gajiminus' => 0,            
            'uangJalantidakterhitung' => 0,            
            'modifiedby' => 'ADMIN',    
        ]);
    }
}
