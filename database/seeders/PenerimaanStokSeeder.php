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

        penerimaanstok::create(['kodepenerimaan' => 'PGDO', 'keterangan' => 'PGDO', 'coa' => '', 'format' => '132', 'statushitungstok' => '177', 'modifiedby' => 'ADMIN', 'urutfifo' => '6',]);
        penerimaanstok::create(['kodepenerimaan' => 'PO', 'keterangan' => 'PO STOK', 'coa' => '', 'format' => '133', 'statushitungstok' => '178', 'modifiedby' => 'ADMIN', 'urutfifo' => '13',]);
        penerimaanstok::create(['kodepenerimaan' => 'SPB', 'keterangan' => 'BELI STOK', 'coa' => '', 'format' => '134', 'statushitungstok' => '177', 'modifiedby' => 'ADMIN', 'urutfifo' => '3',]);
        penerimaanstok::create(['kodepenerimaan' => 'KOR', 'keterangan' => 'KOREKSI STOK', 'coa' => '', 'format' => '136', 'statushitungstok' => '177', 'modifiedby' => 'ADMIN', 'urutfifo' => '4',]);
        penerimaanstok::create(['kodepenerimaan' => 'PG', 'keterangan' => 'PINDAH GUDANG', 'coa' => '', 'format' => '137', 'statushitungstok' => '177', 'modifiedby' => 'ADMIN', 'urutfifo' => '7',]);
        penerimaanstok::create(['kodepenerimaan' => 'SPBS', 'keterangan' => 'PERBAIKAN STOK', 'coa' => '', 'format' => '138', 'statushitungstok' => '177', 'modifiedby' => 'ADMIN', 'urutfifo' => '5',]);
        penerimaanstok::create(['kodepenerimaan' => 'SST', 'keterangan' => 'SALDO STOK TRUCKING', 'coa' => '', 'format' => '145', 'statushitungstok' => '177', 'modifiedby' => 'ADMIN', 'urutfifo' => '1',]);
        penerimaanstok::create(['kodepenerimaan' => 'PST', 'keterangan' => 'PENGEMBALIAN SPAREPART GANTUNG TRUCKING', 'coa' => '', 'format' => '352', 'statushitungstok' => '177', 'modifiedby' => 'ADMIN', 'urutfifo' => '10',]);
        penerimaanstok::create(['kodepenerimaan' => 'PSPK', 'keterangan' => 'PENGEMBALIAN SPK', 'coa' => '', 'format' => '361', 'statushitungstok' => '177', 'modifiedby' => 'ADMIN', 'urutfifo' => '11',]);
        penerimaanstok::create(['kodepenerimaan' => 'KORV', 'keterangan' => 'KOREKSI VULKAN', 'coa' => '', 'format' => '385', 'statushitungstok' => '178', 'modifiedby' => 'ADMIN', 'urutfifo' => '12',]);
        penerimaanstok::create(['kodepenerimaan' => 'SPBP', 'keterangan' => 'PENAMBAHAN NILAI', 'coa' => '', 'format' => '394', 'statushitungstok' => '178', 'modifiedby' => 'ADMIN', 'urutfifo' => '8',]);
    }
}
