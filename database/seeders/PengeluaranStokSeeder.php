<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PengeluaranStok;
use Illuminate\Support\Facades\DB;

class PengeluaranStokSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete PengeluaranStok");
        DB::statement("DBCC CHECKIDENT ('PengeluaranStok', RESEED, 1);");
        PengeluaranStok::create(['kodepengeluaran' => 'SPK', 'keterangan' => 'SPK STOK', 'coa' => '', 'format' => '135', 'statushitungstok' => '177', 'modifiedby' => 'ADMIN',]);
        PengeluaranStok::create(['kodepengeluaran' => 'RTR', 'keterangan' => 'RETUR STOK', 'coa' => '', 'format' => '139', 'statushitungstok' => '177', 'modifiedby' => 'ADMIN',]);
        PengeluaranStok::create(['kodepengeluaran' => 'KOR', 'keterangan' => 'KOREKSI STOK', 'coa' => '', 'format' => '221', 'statushitungstok' => '177', 'modifiedby' => 'ADMIN',]);
        PengeluaranStok::create(['kodepengeluaran' => 'PJA', 'keterangan' => 'PENJUALAN STOK AFKIR', 'coa' => '', 'format' => '340', 'statushitungstok' => '177', 'modifiedby' => 'ADMIN',]);
        PengeluaranStok::create(['kodepengeluaran' => 'GST', 'keterangan' => 'SPAREPART GANTUNG TRUCKING', 'coa' => '', 'format' => '353', 'statushitungstok' => '177', 'modifiedby' => 'ADMIN',]);
        PengeluaranStok::create(['kodepenerimaan' => 'KORV', 'keterangan' => 'KOREKSI VULKAN', 'coa' => '', 'format' => '386', 'statushitungstok' => '178', 'modifiedby' => 'ADMIN',]);
    }
}
