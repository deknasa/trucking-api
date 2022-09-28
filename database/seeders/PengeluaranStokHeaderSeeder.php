<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PengeluaranStokHeader;
use Illuminate\Support\Facades\DB;

class PengeluaranStokHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {


        
        DB::statement("delete PengeluaranStokHeader");
        DB::statement("DBCC CHECKIDENT ('PengeluaranStokHeader', RESEED, 0);");

        PengeluaranStokHeader::create([
            'nobukti' => 'SPK 0001/VIII/2022',
            'tglbukti' => '2022/8/15',
            'pengeluaranstok_id' => 1,
            'penerimaanstok_nobukti' => '',
            'pengeluaranstok_nobukti' => '',
            'servicein_nobukti' => '',
            'gudang_id' => 1,
            'trado_id' => 1,            
            'supplier_id' => 0,            
            'supir_id' => 1,            
            'kerusakan_id' => 1,            
            'keterangan' => 'GANTI YANG RUSAK',
            'modifiedby' => 'ADMIN',
        ]);

        PengeluaranStokHeader::create([
            'nobukti' => 'RBT 0001/VI/2022',
            'tglbukti' => '2022/8/15',
            'pengeluaranstok_id' => 1,
            'penerimaanstok_nobukti' => 'PBT 0001/VI/2022',
            'pengeluaranstok_nobukti' => '',
            'servicein_nobukti' => '',
            'gudang_id' => 0,
            'trado_id' => 0,            
            'supplier_id' => 1,            
            'supir_id' => 0,            
            'kerusakan_id' => 0,            
            'keterangan' => 'RETUR BAUT',
            'modifiedby' => 'ADMIN',
        ]);        
    }
}
