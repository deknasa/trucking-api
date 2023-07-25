<?php

namespace Database\Seeders;
use App\Models\SaldoPengeluaranHeader;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Seeder;

class SaldoPengeluaranHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete saldopengeluaranheader");
        DB::statement("DBCC CHECKIDENT ('saldopengeluaranheader', RESEED, 0);");

        saldopengeluaranheader::create([ 'nobukti' => 'BKT-M BCA3 0009/IV/2023', 'tglbukti' => '2023/4/4', 'keterangan' => 'Pembayaran atas pembelian kepada SAUDARA MOTOR', 'pelanggan_id' => '42', 'alatbayar_id' => '3', 'postingdari' => 'Saldo hutang giro', 'statusapproval' => '3', 'dibayarke' => '', 'bank_id' => '4', 'userapproval' => 'admin', 'tglapproval' => '2023/4/4', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'transferkeac' => '1950923268', 'transferkean' => 'MARIA AMRIN OR PAUL AGUSTIALIUS', 'transferkebank' => 'BCA', 'statusformat' => '269', 'modifiedby' => 'FAdmin',]);
    }
}
