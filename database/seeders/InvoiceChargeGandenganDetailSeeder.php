<?php

namespace Database\Seeders;

use App\Models\InvoiceChargeGandenganDetail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InvoiceChargeGandenganDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete invoicechargegandengandetail");
        DB::statement("DBCC CHECKIDENT ('invoicechargegandengandetail', RESEED, 1);");
        InvoiceChargeGandenganDetail::create(['invoicechargegandengan_id' => '3', 'nobukti' => 'INVG 0001/VII/2023', 'jobtrucking' => '0033/I/23', 'trado_id' => '39', 'gandengan_id' => '63', 'tgltrip' => '2023/1/3', 'tglakhir' => '2023/1/10', 'jumlahhari' => '7', 'nominal' => '600000.00', 'total' => '600000.00', 'keterangan' => ' ', 'jenisorder' => 'BONGKARAN', 'namagudang' => 'MARENDAL', 'modifiedby' => 'ADMIN',]);
        InvoiceChargeGandenganDetail::create(['invoicechargegandengan_id' => '3', 'nobukti' => 'INVG 0001/VII/2023', 'jobtrucking' => '0065/I/23', 'trado_id' => '38', 'gandengan_id' => '39', 'tgltrip' => '2023/1/11', 'tglakhir' => '2023/1/17', 'jumlahhari' => '6', 'nominal' => '300000.00', 'total' => '300000.00', 'keterangan' => ' ', 'jenisorder' => 'IMPORT', 'namagudang' => 'DALU X', 'modifiedby' => 'ADMIN',]);
        InvoiceChargeGandenganDetail::create(['invoicechargegandengan_id' => '3', 'nobukti' => 'INVG 0001/VII/2023', 'jobtrucking' => '0088/II/23', 'trado_id' => '38', 'gandengan_id' => '63', 'tgltrip' => '2023/2/6', 'tglakhir' => '2023/2/13', 'jumlahhari' => '6', 'nominal' => '300000.00', 'total' => '300000.00', 'keterangan' => ' ', 'jenisorder' => 'IMPORT', 'namagudang' => 'BGR SP KNTOR', 'modifiedby' => 'ADMIN',]);
    }
}
