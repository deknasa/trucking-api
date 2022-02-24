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
            'nobukti' => '',            
            'tgl' => '',            
            'supir_id' => '',            
            'nominal' => '',            
            'keterangan' => '',            
            'tgldari' => '',            
            'tglsampai' => '',            
            'total' => '',            
            'uangjalan' => '',            
            'bbm' => '',            
            'potonganpinjaman' => '',            
            'deposito' => '',            
            'potonganpinjamansemua' => '',            
            'komisisupir' => '',            
            'tolsupir' => '',            
            'voucher' => '',            
            'uangmakanharian' => '',            
            'pinjamanpribadi' => '',            
            'gajiminus' => '',            
            'uangJalantidakterhitung' => '',            
            'modifiedby' => 'ADMIN',    
        ]);
    }
}
