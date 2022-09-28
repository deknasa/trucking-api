<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PenerimaanStokHeader;
use Illuminate\Support\Facades\DB;

class PenerimaanStokHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete PenerimaanStokHeader");
        DB::statement("DBCC CHECKIDENT ('PenerimaanStokHeader', RESEED, 0);");

        PenerimaanStokHeader::create([
            'nobukti' => 'DOT 0001/VIII/2022',
            'tglbukti' => '2022/8/15',
            'penerimaanstok_id' => 1,
            'penerimaanstok_nobukti' => '',
            'pengeluaranstok_nobukti' => '',
            'gudang_id' => 1,
            'trado_id' => 0,            
            'supplier_id' => 0,            
            'nobon'  => '',
            'hutang_nobukti'  => '',
            'gudangdari_id'  => 0,
            'gudangke_id'  => 0,            
            'coa' => '',
            'keterangan' => 'PERBAIKAN RADIATOR',
            'modifiedby' => 'ADMIN',
        ]);
        PenerimaanStokHeader::create([
            'nobukti' => 'POT 0001/VI/2022',
            'tglbukti' => '2022/6/14',
            'penerimaanstok_id' => 2,
            'penerimaanstok_nobukti' => '',
            'pengeluaranstok_nobukti' => '',
            'gudang_id' => 0,            
            'trado_id' => 0,            
            'supplier_id' => 1,            
            'nobon'  => '',
            'hutang_nobukti'  => '',
            'gudangdari_id'  => 0,
            'gudangke_id'  => 0,            
            'coa' => '',
            'keterangan' => 'PEMBELIAN BAUT',
            'modifiedby' => 'ADMIN',
        ]);
        PenerimaanStokHeader::create([
            'nobukti' => 'PBT 0001/VII/2022',
            'tglbukti' => '2022/7/1',
            'penerimaanstok_id' => 3,
            'penerimaanstok_nobukti' => 'POT 0001/VI/2022',
            'pengeluaranstok_nobukti' => '',
            'gudang_id' => 0,            
            'trado_id' => 0,            
            'supplier_id' => 1,            
            'nobon'  => '',
            'hutang_nobukti'  => '',
            'gudangdari_id'  => 0,
            'gudangke_id'  => 0,            
            'coa' => '',
            'keterangan' => 'PEMBELIAN BAUT',
            'modifiedby' => 'ADMIN',
        ]);

        PenerimaanStokHeader::create([
            'nobukti' => 'KST 0001/VIII/2022',
            'tglbukti' => '2022/8/15',
            'penerimaanstok_id' => 4,
            'penerimaanstok_nobukti' => '',
            'pengeluaranstok_nobukti' => '',
            'gudang_id' => 1,            
            'trado_id' => 0,            
            'supplier_id' => 0,            
            'nobon'  => '',
            'hutang_nobukti'  => '',
            'gudangdari_id'  => 0,
            'gudangke_id'  => 0,            
            'coa' => '',
            'keterangan' => 'OPNAME STOCK',
            'modifiedby' => 'ADMIN',
        ]);        

        PenerimaanStokHeader::create([
            'nobukti' => 'PGT 0001/VIII/2022',
            'tglbukti' => '2022/8/15',
            'penerimaanstok_id' => 5,
            'penerimaanstok_nobukti' => '',
            'pengeluaranstok_nobukti' => '',
            'gudang_id' => 0,            
            'trado_id' => 0,            
            'supplier_id' => 0,            
            'nobon'  => '',
            'hutang_nobukti'  => '',
            'gudangdari_id'  => 1,
            'gudangke_id'  => 3,            
            'coa' => '',
            'keterangan' => 'PINDAH GUDANG',
            'modifiedby' => 'ADMIN',
        ]);   
        
        PenerimaanStokHeader::create([
            'nobukti' => 'PST 0001/VIII/2022',
            'tglbukti' => '2022/8/15',
            'penerimaanstok_id' => 6,
            'penerimaanstok_nobukti' => 'DOT 0001/VIII/2022',
            'pengeluaranstok_nobukti' => '',
            'gudang_id' => 0,            
            'trado_id' => 0,            
            'supplier_id' => 1,            
            'nobon'  => '',
            'hutang_nobukti'  => '',
            'gudangdari_id'  => 0,
            'gudangke_id'  => 0,            
            'coa' => '',
            'keterangan' => 'PERBAIKAN RADIATOR',
            'modifiedby' => 'ADMIN',
        ]);             

    }
}
