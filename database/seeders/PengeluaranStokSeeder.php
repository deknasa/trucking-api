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
        PengeluaranStok::create(['kodepengeluaran' => 'SPK', 'keterangan' => 'SPK STOK', 'coa' => '', 'statusformat' => '135', 'statushitungstok' => '177', 'modifiedby' => 'ADMIN',]);
        PengeluaranStok::create(['kodepengeluaran' => 'RBT', 'keterangan' => 'RETUR STOK', 'coa' => '', 'statusformat' => '139', 'statushitungstok' => '177', 'modifiedby' => 'ADMIN',]);
    }
}
