<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PenerimaanStokDetail;
use Illuminate\Support\Facades\DB;

class PenerimaanStokDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("ALTER TABLE PenerimaanStokDetail CHECK CONSTRAINT penerimaanstokdetail_penerimaanstokheader_id_foreign");
        DB::statement("ALTER TABLE PenerimaanStokDetail NOCHECK CONSTRAINT penerimaanstokdetail_stok_id_foreign");

        DB::statement("delete PenerimaanStokDetail");
        DB::statement("DBCC CHECKIDENT ('PenerimaanStokDetail', RESEED, 0);");

        PenerimaanStokDetail::create([
            'penerimaanstokheader_id' => 1,
            'nobukti' => 'DOT 0001/VIiI/2022',
            'stok_id' => 2,
            'conv1' => 1,
            'conv2' => 1,
            'qty0' => 0,
            'qty1' => 0,
            'qty2' => 1,
            'totalqty' => 1,
            'harga0' => 0,
            'harga1' => 0,
            'harga2' => 0,
            'persentasediscount' => 0,
            'nominaldiscount' => 0,
            'total' => 0,
            'keterangan' => 'PERBAIKAN RADIATOR',
            'vulkanisirke' => '',
            'modifiedby' => 'ADMIN',
        ]);

        PenerimaanStokDetail::create([
            'penerimaanstokheader_id' => 2,
            'nobukti' => 'POT 0001/VI/2022',
            'stok_id' => 1,
            'conv1' => 1,
            'conv2' => 1,
            'qty0' => 0,
            'qty1' => 0,
            'qty2' => 10,
            'totalqty' => 10,
            'harga0' => 0,
            'harga1' => 0,
            'harga2' => 500,
            'persentasediscount' => 0,
            'nominaldiscount' => 0,
            'total' => 5000,
            'keterangan' => 'PEMBELIAN BAUT',
            'vulkanisirke' => '',
            'modifiedby' => 'ADMIN',
        ]);        

        PenerimaanStokDetail::create([
            'penerimaanstokheader_id' => 3,
            'nobukti' => 'PBT 0001/VII/2022',
            'stok_id' => 1,
            'conv1' => 1,
            'conv2' => 1,
            'qty0' => 0,
            'qty1' => 0,
            'qty2' => 10,
            'totalqty' => 10,
            'harga0' => 0,
            'harga1' => 0,
            'harga2' => 500,
            'persentasediscount' => 0,
            'nominaldiscount' => 0,
            'total' => 5000,
            'keterangan' => 'PEMBELIAN BAUT',
            'vulkanisirke' => '',
            'modifiedby' => 'ADMIN',
        ]);         

        PenerimaanStokDetail::create([
            'penerimaanstokheader_id' => 4,
            'nobukti' => 'KST 0001/VIII/2022',
            'stok_id' => 1,
            'conv1' => 1,
            'conv2' => 1,
            'qty0' => 0,
            'qty1' => 0,
            'qty2' => 2,
            'totalqty' => 2,
            'harga0' => 0,
            'harga1' => 0,
            'harga2' => 500,
            'persentasediscount' => 0,
            'nominaldiscount' => 0,
            'total' => 1000,
            'keterangan' => 'OPNAME STOCK',
            'vulkanisirke' => '',
            'modifiedby' => 'ADMIN',
        ]);         

        PenerimaanStokDetail::create([
            'penerimaanstokheader_id' => 5,
            'nobukti' => 'PGT 0001/VIII/2022',
            'stok_id' => 2,
            'conv1' => 1,
            'conv2' => 1,
            'qty0' => 0,
            'qty1' => 0,
            'qty2' => 1,
            'totalqty' => 1,
            'harga0' => 0,
            'harga1' => 0,
            'harga2' => 0,
            'persentasediscount' => 0,
            'nominaldiscount' => 0,
            'total' => 0,
            'keterangan' => 'PINDAH GUDANG',
            'vulkanisirke' => '',
            'modifiedby' => 'ADMIN',
        ]);   

        PenerimaanStokDetail::create([
            'penerimaanstokheader_id' => 6,
            'nobukti' => 'PST 0001/VIII/2022',
            'stok_id' => 2,
            'conv1' => 1,
            'conv2' => 1,
            'qty0' => 0,
            'qty1' => 0,
            'qty2' => 1,
            'totalqty' => 1,
            'harga0' => 0,
            'harga1' => 0,
            'harga2' => 200000,
            'persentasediscount' => 0,
            'nominaldiscount' => 0,
            'total' => 200000,
            'keterangan' => 'PERBAIKAN RADIATOR',
            'vulkanisirke' => '',
            'modifiedby' => 'ADMIN',
        ]);   

    }
}
