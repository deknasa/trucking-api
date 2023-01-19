<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PenerimaanStok;
use Illuminate\Support\Facades\DB;

class PenerimaanStokSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete PenerimaanStok");
        DB::statement("DBCC CHECKIDENT ('PenerimaanStok', RESEED, 1);");

        PenerimaanStok::create(['kodepenerimaan' => 'DOT', 'keterangan' => 'DELIVERY ORDER', 'coa' => '', 'format' => '132', 'statushitungstok' => '178', 'modifiedby' => 'ADMIN',]);
        PenerimaanStok::create(['kodepenerimaan' => 'POT', 'keterangan' => 'PO STOK', 'coa' => '', 'format' => '133', 'statushitungstok' => '178', 'modifiedby' => 'ADMIN',]);
        PenerimaanStok::create(['kodepenerimaan' => 'PBT', 'keterangan' => 'BELI STOK', 'coa' => '', 'format' => '134', 'statushitungstok' => '177', 'modifiedby' => 'ADMIN',]);
        PenerimaanStok::create(['kodepenerimaan' => 'KST', 'keterangan' => 'KOREKSI STOK', 'coa' => '', 'format' => '136', 'statushitungstok' => '177', 'modifiedby' => 'ADMIN',]);
        PenerimaanStok::create(['kodepenerimaan' => 'PGT', 'keterangan' => 'PINDAH GUDANG', 'coa' => '', 'format' => '137', 'statushitungstok' => '177', 'modifiedby' => 'ADMIN',]);
        PenerimaanStok::create(['kodepenerimaan' => 'PST', 'keterangan' => 'PERBAIKAN STOK', 'coa' => '', 'format' => '138', 'statushitungstok' => '177', 'modifiedby' => 'ADMIN',]);
        PenerimaanStok::create(['kodepenerimaan' => 'SST', 'keterangan' => 'SALDO STOK TRUCKING', 'coa' => '', 'format' => '138', 'statushitungstok' => '177', 'modifiedby' => 'ADMIN',]);
    }
}
