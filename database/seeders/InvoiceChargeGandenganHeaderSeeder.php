<?php

namespace Database\Seeders;

use App\Models\InvoiceChargeGandenganHeader;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InvoiceChargeGandenganHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete invoicechargegandenganheader");
        DB::statement("DBCC CHECKIDENT ('invoicechargegandenganheader', RESEED, 1);");

        InvoiceChargeGandenganHeader::create([ 'nobukti' => 'INVG 0001/VII/2023', 'tglbukti' => '2023/7/21', 'tglproses' => '2023/7/21', 'keterangan' => '', 'agen_id' => '64', 'nominal' => '1200000.00', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'statusformat' => '300', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);    
    }
}
