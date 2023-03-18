<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InvoiceDetail;
use Illuminate\Support\Facades\DB;

class InvoiceDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete invoicedetail");
        DB::statement("DBCC CHECKIDENT ('invoicedetail', RESEED, 1);");

        invoicedetail::create(['invoice_id' => '1', 'nobukti' => 'INV 0001/II/2023', 'nominal' => '1431250', 'nominalretribusi' => '0', 'total' => '1431250', 'keterangan' => '', 'modifiedby' => 'ADMIN', 'orderantrucking_nobukti' => '0006/II/23', 'suratpengantar_nobukti' => 'TRP 0011/II/2023,TRP 0038/II/2023,',]);
        invoicedetail::create(['invoice_id' => '1', 'nobukti' => 'INV 0001/II/2023', 'nominal' => '1277000', 'nominalretribusi' => '0', 'total' => '1277000', 'keterangan' => '', 'modifiedby' => 'ADMIN', 'orderantrucking_nobukti' => '0007/II/23', 'suratpengantar_nobukti' => 'TRP 0013/II/2023,TRP 0040/II/2023,',]);
        invoicedetail::create(['invoice_id' => '1', 'nobukti' => 'INV 0001/II/2023', 'nominal' => '1277000', 'nominalretribusi' => '0', 'total' => '1277000', 'keterangan' => '', 'modifiedby' => 'ADMIN', 'orderantrucking_nobukti' => '0008/II/23', 'suratpengantar_nobukti' => 'TRP 0015/II/2023,TRP 0044/II/2023,',]);
        invoicedetail::create(['invoice_id' => '1', 'nobukti' => 'INV 0001/II/2023', 'nominal' => '1431250', 'nominalretribusi' => '0', 'total' => '1431250', 'keterangan' => '', 'modifiedby' => 'ADMIN', 'orderantrucking_nobukti' => '0009/II/23', 'suratpengantar_nobukti' => 'TRP 0017/II/2023,TRP 0042/II/2023,',]);
        invoicedetail::create(['invoice_id' => '1', 'nobukti' => 'INV 0001/II/2023', 'nominal' => '2400000', 'nominalretribusi' => '0', 'total' => '2400000', 'keterangan' => '', 'modifiedby' => 'ADMIN', 'orderantrucking_nobukti' => '0012/II/23', 'suratpengantar_nobukti' => 'TRP 0024/II/2023,TRP 0195/II/2023,',]);
        invoicedetail::create(['invoice_id' => '1', 'nobukti' => 'INV 0001/II/2023', 'nominal' => '2400000', 'nominalretribusi' => '0', 'total' => '2400000', 'keterangan' => '', 'modifiedby' => 'ADMIN', 'orderantrucking_nobukti' => '0015/II/23', 'suratpengantar_nobukti' => 'TRP 0029/II/2023,TRP 0106/II/2023,',]);
        invoicedetail::create(['invoice_id' => '1', 'nobukti' => 'INV 0001/II/2023', 'nominal' => '1277000', 'nominalretribusi' => '0', 'total' => '1277000', 'keterangan' => '', 'modifiedby' => 'ADMIN', 'orderantrucking_nobukti' => '0018/II/23', 'suratpengantar_nobukti' => 'TRP 0036/II/2023,TRP 0093/II/2023,TRP 0129/II/2023,',]);
        invoicedetail::create(['invoice_id' => '1', 'nobukti' => 'INV 0001/II/2023', 'nominal' => '1432000', 'nominalretribusi' => '0', 'total' => '1432000', 'keterangan' => '', 'modifiedby' => 'ADMIN', 'orderantrucking_nobukti' => '0019/II/23', 'suratpengantar_nobukti' => 'TRP 0039/II/2023,TRP 0084/II/2023,TRP 0120/II/2023,',]);
        invoicedetail::create(['invoice_id' => '1', 'nobukti' => 'INV 0001/II/2023', 'nominal' => '1277000', 'nominalretribusi' => '0', 'total' => '1277000', 'keterangan' => '', 'modifiedby' => 'ADMIN', 'orderantrucking_nobukti' => '0020/II/23', 'suratpengantar_nobukti' => 'TRP 0041/II/2023,TRP 0085/II/2023,',]);
        invoicedetail::create(['invoice_id' => '1', 'nobukti' => 'INV 0001/II/2023', 'nominal' => '1431250', 'nominalretribusi' => '0', 'total' => '1431250', 'keterangan' => '', 'modifiedby' => 'ADMIN', 'orderantrucking_nobukti' => '0021/II/23', 'suratpengantar_nobukti' => 'TRP 0043/II/2023,TRP 0087/II/2023,',]);
        invoicedetail::create(['invoice_id' => '1', 'nobukti' => 'INV 0001/II/2023', 'nominal' => '1431250', 'nominalretribusi' => '0', 'total' => '1431250', 'keterangan' => '', 'modifiedby' => 'ADMIN', 'orderantrucking_nobukti' => '0022/II/23', 'suratpengantar_nobukti' => 'TRP 0045/II/2023,TRP 0089/II/2023,',]);
        invoicedetail::create(['invoice_id' => '1', 'nobukti' => 'INV 0001/II/2023', 'nominal' => '2400000', 'nominalretribusi' => '0', 'total' => '2400000', 'keterangan' => '', 'modifiedby' => 'ADMIN', 'orderantrucking_nobukti' => '0028/II/23', 'suratpengantar_nobukti' => 'TRP 0058/II/2023,TRP 0110/II/2023,TRP 0150/II/2023,',]);
        invoicedetail::create(['invoice_id' => '1', 'nobukti' => 'INV 0001/II/2023', 'nominal' => '1830000', 'nominalretribusi' => '0', 'total' => '1830000', 'keterangan' => '', 'modifiedby' => 'ADMIN', 'orderantrucking_nobukti' => '0032/II/23', 'suratpengantar_nobukti' => 'TRP 0065/II/2023,TRP 0211/II/2023,',]);
        invoicedetail::create(['invoice_id' => '1', 'nobukti' => 'INV 0001/II/2023', 'nominal' => '1277000', 'nominalretribusi' => '0', 'total' => '1277000', 'keterangan' => '', 'modifiedby' => 'ADMIN', 'orderantrucking_nobukti' => '0035/II/23', 'suratpengantar_nobukti' => 'TRP 0070/II/2023,TRP 0094/II/2023,TRP 0132/II/2023,',]);
        invoicedetail::create(['invoice_id' => '1', 'nobukti' => 'INV 0001/II/2023', 'nominal' => '2400000', 'nominalretribusi' => '0', 'total' => '2400000', 'keterangan' => '', 'modifiedby' => 'ADMIN', 'orderantrucking_nobukti' => '0038/II/23', 'suratpengantar_nobukti' => 'TRP 0078/II/2023,TRP 0147/II/2023,TRP 0158/II/2023,',]);
        invoicedetail::create(['invoice_id' => '1', 'nobukti' => 'INV 0001/II/2023', 'nominal' => '1117000', 'nominalretribusi' => '0', 'total' => '1117000', 'keterangan' => '', 'modifiedby' => 'ADMIN', 'orderantrucking_nobukti' => '0041/II/23', 'suratpengantar_nobukti' => 'TRP 0083/II/2023,TRP 0111/II/2023,',]);
        invoicedetail::create(['invoice_id' => '1', 'nobukti' => 'INV 0001/II/2023', 'nominal' => '1432000', 'nominalretribusi' => '0', 'total' => '1432000', 'keterangan' => '', 'modifiedby' => 'ADMIN', 'orderantrucking_nobukti' => '0042/II/23', 'suratpengantar_nobukti' => 'TRP 0086/II/2023,TRP 0124/II/2023,',]);
        invoicedetail::create(['invoice_id' => '1', 'nobukti' => 'INV 0001/II/2023', 'nominal' => '892500', 'nominalretribusi' => '0', 'total' => '892500', 'keterangan' => '', 'modifiedby' => 'ADMIN', 'orderantrucking_nobukti' => '0043/II/23', 'suratpengantar_nobukti' => 'TRP 0088/II/2023,TRP 0126/II/2023,',]);
        invoicedetail::create(['invoice_id' => '1', 'nobukti' => 'INV 0001/II/2023', 'nominal' => '1117000', 'nominalretribusi' => '0', 'total' => '1117000', 'keterangan' => '', 'modifiedby' => 'ADMIN', 'orderantrucking_nobukti' => '0048/II/23', 'suratpengantar_nobukti' => 'TRP 0097/II/2023,TRP 0136/II/2023,',]);
        invoicedetail::create(['invoice_id' => '1', 'nobukti' => 'INV 0001/II/2023', 'nominal' => '1277000', 'nominalretribusi' => '0', 'total' => '1277000', 'keterangan' => '', 'modifiedby' => 'ADMIN', 'orderantrucking_nobukti' => '0051/II/23', 'suratpengantar_nobukti' => 'TRP 0103/II/2023,TRP 0192/II/2023,',]);
        invoicedetail::create(['invoice_id' => '1', 'nobukti' => 'INV 0001/II/2023', 'nominal' => '2400000', 'nominalretribusi' => '0', 'total' => '2400000', 'keterangan' => '', 'modifiedby' => 'ADMIN', 'orderantrucking_nobukti' => '0053/II/23', 'suratpengantar_nobukti' => 'TRP 0107/II/2023,TRP 0145/II/2023,TRP 0181/II/2023,',]);
        invoicedetail::create(['invoice_id' => '1', 'nobukti' => 'INV 0001/II/2023', 'nominal' => '2400000', 'nominalretribusi' => '0', 'total' => '2400000', 'keterangan' => '', 'modifiedby' => 'ADMIN', 'orderantrucking_nobukti' => '0054/II/23', 'suratpengantar_nobukti' => 'TRP 0109/II/2023,TRP 0160/II/2023,',]);
        invoicedetail::create(['invoice_id' => '1', 'nobukti' => 'INV 0001/II/2023', 'nominal' => '892500', 'nominalretribusi' => '0', 'total' => '892500', 'keterangan' => '', 'modifiedby' => 'ADMIN', 'orderantrucking_nobukti' => '0055/II/23', 'suratpengantar_nobukti' => 'TRP 0112/II/2023,TRP 0179/II/2023,',]);
        invoicedetail::create(['invoice_id' => '1', 'nobukti' => 'INV 0001/II/2023', 'nominal' => '1432000', 'nominalretribusi' => '0', 'total' => '1432000', 'keterangan' => '', 'modifiedby' => 'ADMIN', 'orderantrucking_nobukti' => '0058/II/23', 'suratpengantar_nobukti' => 'TRP 0116/II/2023,TRP 0172/II/2023,TRP 0173/II/2023,',]);
        invoicedetail::create(['invoice_id' => '1', 'nobukti' => 'INV 0001/II/2023', 'nominal' => '892500', 'nominalretribusi' => '0', 'total' => '892500', 'keterangan' => '', 'modifiedby' => 'ADMIN', 'orderantrucking_nobukti' => '0060/II/23', 'suratpengantar_nobukti' => 'TRP 0118/II/2023,TRP 0119/II/2023,TRP 0155/II/2023,',]);
        invoicedetail::create(['invoice_id' => '1', 'nobukti' => 'INV 0001/II/2023', 'nominal' => '1277000', 'nominalretribusi' => '0', 'total' => '1277000', 'keterangan' => '', 'modifiedby' => 'ADMIN', 'orderantrucking_nobukti' => '0061/II/23', 'suratpengantar_nobukti' => 'TRP 0121/II/2023,TRP 0122/II/2023,',]);
        invoicedetail::create(['invoice_id' => '1', 'nobukti' => 'INV 0001/II/2023', 'nominal' => '1431250', 'nominalretribusi' => '0', 'total' => '1431250', 'keterangan' => '', 'modifiedby' => 'ADMIN', 'orderantrucking_nobukti' => '0062/II/23', 'suratpengantar_nobukti' => 'TRP 0123/II/2023,TRP 0162/II/2023,TRP 0197/II/2023,',]);
        invoicedetail::create(['invoice_id' => '1', 'nobukti' => 'INV 0001/II/2023', 'nominal' => '1431250', 'nominalretribusi' => '0', 'total' => '1431250', 'keterangan' => '', 'modifiedby' => 'ADMIN', 'orderantrucking_nobukti' => '0063/II/23', 'suratpengantar_nobukti' => 'TRP 0125/II/2023,TRP 0163/II/2023,TRP 0199/II/2023,',]);
        invoicedetail::create(['invoice_id' => '1', 'nobukti' => 'INV 0001/II/2023', 'nominal' => '1431250', 'nominalretribusi' => '0', 'total' => '1431250', 'keterangan' => '', 'modifiedby' => 'ADMIN', 'orderantrucking_nobukti' => '0064/II/23', 'suratpengantar_nobukti' => 'TRP 0127/II/2023,TRP 0166/II/2023,TRP 0201/II/2023,',]);
        invoicedetail::create(['invoice_id' => '1', 'nobukti' => 'INV 0001/II/2023', 'nominal' => '1277000', 'nominalretribusi' => '0', 'total' => '1277000', 'keterangan' => '', 'modifiedby' => 'ADMIN', 'orderantrucking_nobukti' => '0065/II/23', 'suratpengantar_nobukti' => 'TRP 0131/II/2023,TRP 0185/II/2023,',]);
        invoicedetail::create(['invoice_id' => '1', 'nobukti' => 'INV 0001/II/2023', 'nominal' => '892500', 'nominalretribusi' => '0', 'total' => '892500', 'keterangan' => '', 'modifiedby' => 'ADMIN', 'orderantrucking_nobukti' => '0067/II/23', 'suratpengantar_nobukti' => 'TRP 0135/II/2023,TRP 0169/II/2023,',]);
        invoicedetail::create(['invoice_id' => '1', 'nobukti' => 'INV 0001/II/2023', 'nominal' => '2250000', 'nominalretribusi' => '0', 'total' => '2250000', 'keterangan' => '', 'modifiedby' => 'ADMIN', 'orderantrucking_nobukti' => '0069/II/23', 'suratpengantar_nobukti' => 'TRP 0140/II/2023,TRP 0177/II/2023,TRP 0178/II/2023,TRP 0213/II/2023,',]);
    }
}
