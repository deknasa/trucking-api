<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PengeluaranStokDetail;
use Illuminate\Support\Facades\DB;

class PengeluaranStokDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {


        DB::statement("delete PengeluaranStokDetail");
        DB::statement("DBCC CHECKIDENT ('PengeluaranStokDetail', RESEED, 1);");

        PengeluaranStokDetail::create(['pengeluaranstokheader_id' => '1', 'nobukti' => 'SPK 0001/VIII/2022', 'stok_id' => '1', 'qty' => '2', 'harga' => '500', 'persentasediscount' => '0', 'nominaldiscount' => '0', 'total' => '10000', 'keterangan' => 'PEMAKAIAN BARANG', 'vulkanisirke' => '0', 'modifiedby' => 'ADMIN',]);
        PengeluaranStokDetail::create(['pengeluaranstokheader_id' => '2', 'nobukti' => 'RBT 0001/VII/2022', 'stok_id' => '1', 'qty' => '1', 'harga' => '500', 'persentasediscount' => '0', 'nominaldiscount' => '0', 'total' => '500', 'keterangan' => 'RETUR PEMBELIAN BAUT', 'vulkanisirke' => '0', 'modifiedby' => 'ADMIN',]);

    }
}
