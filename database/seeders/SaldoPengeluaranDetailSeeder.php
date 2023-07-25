<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SaldoPengeluaranDetail;
use Illuminate\Support\Facades\DB;


class SaldoPengeluaranDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete saldopengeluarandetail");
        DB::statement("DBCC CHECKIDENT ('saldopengeluarandetail', RESEED, 1);");


        saldopengeluarandetail::create(['saldopengeluaran_id' => '1', 'nobukti' => 'BKT-M BCA3 0009/IV/2023', 'nowarkat' => 'DX 732593', 'tgljatuhtempo' => '2023/4/7', 'nominal' => '7454900.00', 'coadebet' => '03.02.02.01', 'coakredit' => '01.02.02.05', 'keterangan' => 'Pembayaran atas pembelian kepada SAUDARA MOTOR', 'noinvoice' => '', 'bank' => '', 'bulanbeban' => '1900/1/1', 'modifiedby' => 'FAdmin',]);
    }
}
