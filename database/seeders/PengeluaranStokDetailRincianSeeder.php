<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use App\Models\PengeluaranStokDetailRincian;
use Illuminate\Support\Facades\DB;

class PengeluaranStokDetailRincianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete PengeluaranStokDetailRincian");
        DB::statement("DBCC CHECKIDENT ('PengeluaranStokDetailRincian', RESEED, 1);");

        PengeluaranStokDetailRincian::create([ 'pengeluaranstokheader_id' => '1', 'nobukti' => 'SPK 0001/VIII/2022', 'stok_id' => '1', 'qty' => '1', 'saldoqty' => '0', 'penerimaanstok_nobukti' => '', 'penerimaanstok_harga' => '0', 'modifiedby' => 'ADMIN',]);
        PengeluaranStokDetailRincian::create([ 'pengeluaranstokheader_id' => '1', 'nobukti' => 'SPK 0001/VIII/2022', 'stok_id' => '1', 'qty' => '1', 'saldoqty' => '0', 'penerimaanstok_nobukti' => '', 'penerimaanstok_harga' => '0', 'modifiedby' => 'ADMIN',]);
        PengeluaranStokDetailRincian::create([ 'pengeluaranstokheader_id' => '2', 'nobukti' => 'RBT 0001/VII/2022', 'stok_id' => '1', 'qty' => '1', 'saldoqty' => '0', 'penerimaanstok_nobukti' => '', 'penerimaanstok_harga' => '0', 'modifiedby' => 'ADMIN',]);

    }
}
