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

        PenerimaanStok::create(['kodepenerimaan' => 'PGDO', 'keterangan' => 'PGDO', 'coa' => '', 'format' => '132', 'statushitungstok' => '177', 'modifiedby' => 'ADMIN',]);
        PenerimaanStok::create(['kodepenerimaan' => 'PO', 'keterangan' => 'PO STOK', 'coa' => '', 'format' => '133', 'statushitungstok' => '178', 'modifiedby' => 'ADMIN',]);
        PenerimaanStok::create(['kodepenerimaan' => 'SPB', 'keterangan' => 'BELI STOK', 'coa' => '', 'format' => '134', 'statushitungstok' => '177', 'modifiedby' => 'ADMIN',]);
        PenerimaanStok::create(['kodepenerimaan' => 'KOR', 'keterangan' => 'KOREKSI STOK', 'coa' => '', 'format' => '136', 'statushitungstok' => '177', 'modifiedby' => 'ADMIN',]);
        PenerimaanStok::create(['kodepenerimaan' => 'PG', 'keterangan' => 'PINDAH GUDANG', 'coa' => '', 'format' => '137', 'statushitungstok' => '177', 'modifiedby' => 'ADMIN',]);
        PenerimaanStok::create(['kodepenerimaan' => 'SPBS', 'keterangan' => 'PERBAIKAN STOK', 'coa' => '', 'format' => '138', 'statushitungstok' => '177', 'modifiedby' => 'ADMIN',]);
        PenerimaanStok::create(['kodepenerimaan' => 'SST', 'keterangan' => 'SALDO STOK TRUCKING', 'coa' => '', 'format' => '145', 'statushitungstok' => '177', 'modifiedby' => 'ADMIN',]);
        PenerimaanStok::create(['kodepenerimaan' => 'PST', 'keterangan' => 'PENGEMBALIAN SPAREPART GANTUNG TRUCKING', 'coa' => '', 'format' => '352', 'statushitungstok' => '177', 'modifiedby' => 'ADMIN',]);
    }
}
