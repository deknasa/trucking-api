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
        DB::statement("DBCC CHECKIDENT ('PengeluaranStokHeader', RESEED, 1);");

        PengeluaranStokHeader::create(['nobukti' => 'SPK 0001/VIII/2022', 'tglbukti' => '2022/8/15', 'keterangan' => 'GANTI YANG RUSAK', 'pengeluaranstok_id' => '1', 'trado_id' => '1', 'gudang_id' => '1', 'supir_id' => '1', 'supplier_id' => '0', 'pengeluaranstok_nobukti' => '', 'penerimaanstok_nobukti' => '', 'servicein_nobukti' => '', 'kerusakan_id' => '1', 'statusformat' => '0', 'modifiedby' => 'ADMIN',]);
        PengeluaranStokHeader::create(['nobukti' => 'RBT 0001/VI/2022', 'tglbukti' => '2022/8/15', 'keterangan' => 'RETUR BAUT', 'pengeluaranstok_id' => '1', 'trado_id' => '0', 'gudang_id' => '0', 'supir_id' => '0', 'supplier_id' => '1', 'pengeluaranstok_nobukti' => '', 'penerimaanstok_nobukti' => 'PBT 0001/VI/2022', 'servicein_nobukti' => '', 'kerusakan_id' => '0', 'statusformat' => '0', 'modifiedby' => 'ADMIN',]);
        PengeluaranStokHeader::create(['nobukti' => 'SPK 0001/X/2022', 'tglbukti' => '2022/10/1', 'keterangan' => 'GANTI SARINGAN OLI', 'pengeluaranstok_id' => '1', 'trado_id' => '1', 'gudang_id' => '1', 'supir_id' => '1', 'supplier_id' => '0', 'pengeluaranstok_nobukti' => '', 'penerimaanstok_nobukti' => '', 'servicein_nobukti' => '', 'kerusakan_id' => '3', 'statusformat' => '0', 'modifiedby' => 'ADMIN',]);
        PengeluaranStokHeader::create(['nobukti' => 'SPK 0002/X/2022', 'tglbukti' => '2022/10/3', 'keterangan' => 'GANTI SARINGAN OLI', 'pengeluaranstok_id' => '1', 'trado_id' => '1', 'gudang_id' => '1', 'supir_id' => '1', 'supplier_id' => '0', 'pengeluaranstok_nobukti' => '', 'penerimaanstok_nobukti' => '', 'servicein_nobukti' => '', 'kerusakan_id' => '3', 'statusformat' => '0', 'modifiedby' => 'ADMIN',]);
        PengeluaranStokHeader::create(['nobukti' => 'SPK 0003/X/2022', 'tglbukti' => '2022/10/5', 'keterangan' => 'GANTI SARINGAN OLI', 'pengeluaranstok_id' => '1', 'trado_id' => '1', 'gudang_id' => '1', 'supir_id' => '1', 'supplier_id' => '0', 'pengeluaranstok_nobukti' => '', 'penerimaanstok_nobukti' => '', 'servicein_nobukti' => '', 'kerusakan_id' => '3', 'statusformat' => '0', 'modifiedby' => 'ADMIN',]);
        PengeluaranStokHeader::create(['nobukti' => 'SPK 0004/X/2022', 'tglbukti' => '2022/10/5', 'keterangan' => 'GANTI SARINGAN OLI', 'pengeluaranstok_id' => '1', 'trado_id' => '1', 'gudang_id' => '1', 'supir_id' => '1', 'supplier_id' => '0', 'pengeluaranstok_nobukti' => '', 'penerimaanstok_nobukti' => '', 'servicein_nobukti' => '', 'kerusakan_id' => '3', 'statusformat' => '0', 'modifiedby' => 'ADMIN',]);

    }
}
