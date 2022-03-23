<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\GajiSupirPelunasanPinjaman;

class GajiSupirPelunasanPinjamanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        GajiSupirPelunasanPinjaman::create([
            'gajisupir_id' => 1,
            'nobukti' => 'RIC 0001/III/2022',            
            'tgl' => '2022/3/21',   
            'pinjaman_nobukti' => 'PJT 0001/III/2022',            
            'pinjamanpengembalian_nobukti' => 'PJP 0001/III/2022',            
            'keterangan' => 'PENGEMBALIAN PINJAMAN',
            'supir_id' => 1,
            'nominal' => 10000,        
            'modifiedby' => 'ADMIN',    
        ]);
    }
}
