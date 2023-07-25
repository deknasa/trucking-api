<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PengeluaranDetail;
use Illuminate\Support\Facades\DB;

class PengeluaranDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {



        DB::statement("delete pengeluarandetail");
        DB::statement("DBCC CHECKIDENT ('pengeluarandetail', RESEED, 1);");


        pengeluarandetail::create(['pengeluaran_id' => '1', 'nobukti' => 'BKT-M BCA3 0009/IV/2023', 'nowarkat' => 'DX 732593', 'tgljatuhtempo' => '2023/4/7', 'nominal' => '7454900.00', 'coadebet' => '03.02.02.01', 'coakredit' => '01.02.02.05', 'keterangan' => 'Pembayaran atas pembelian kepada SAUDARA MOTOR', 'noinvoice' => '', 'bank' => '', 'bulanbeban' => '1900/1/1', 'modifiedby' => 'FAdmin',]);
    }
}
