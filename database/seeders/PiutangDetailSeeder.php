<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PiutangDetail;
use Illuminate\Support\Facades\DB;

class PiutangDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete piutangdetail");
        DB::statement("DBCC CHECKIDENT ('piutangdetail', RESEED, 1);");

        piutangdetail::create(['piutang_id' => '1', 'nobukti' => 'EPT 0001/IV/2023', 'nominal' => '26400000.00', 'keterangan' => 'SALDO PIUTANG PER TGL 01-05-2023', 'invoice_nobukti' => 'INV 0001/IV/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '2', 'nobukti' => 'EPT 0002/IV/2023', 'nominal' => '71021750.00', 'keterangan' => 'SALDO PIUTANG PER TGL 01-05-2023', 'invoice_nobukti' => 'INV 0002/IV/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '3', 'nobukti' => 'EPT 0003/IV/2023', 'nominal' => '22028000.00', 'keterangan' => 'SALDO PIUTANG PER TGL 01-05-2023', 'invoice_nobukti' => 'INV 0003/IV/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '4', 'nobukti' => 'EPT 0004/IV/2023', 'nominal' => '54913000.00', 'keterangan' => 'SALDO PIUTANG PER TGL 01-05-2023', 'invoice_nobukti' => 'INV 0004/IV/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '5', 'nobukti' => 'EPT 0005/IV/2023', 'nominal' => '16400000.00', 'keterangan' => 'SALDO PIUTANG PER TGL 01-05-2023', 'invoice_nobukti' => 'INV 0005/IV/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '6', 'nobukti' => 'EPT 0006/III/2023', 'nominal' => '7315000.00', 'keterangan' => 'SALDO PIUTANG PER TGL 01-05-2023', 'invoice_nobukti' => 'INV 0006/III/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '7', 'nobukti' => 'EPT 0006/IV/2023', 'nominal' => '12500000.00', 'keterangan' => 'SALDO PIUTANG PER TGL 01-05-2023', 'invoice_nobukti' => 'INV 0006/IV/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '8', 'nobukti' => 'EPT 0007/III/2023', 'nominal' => '9020000.00', 'keterangan' => 'SALDO PIUTANG PER TGL 01-05-2023', 'invoice_nobukti' => 'INV 0007/III/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '9', 'nobukti' => 'EPT 0007/IV/2023', 'nominal' => '13940000.00', 'keterangan' => 'SALDO PIUTANG PER TGL 01-05-2023', 'invoice_nobukti' => 'INV 0007/IV/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '10', 'nobukti' => 'EPT 0008/IV/2023', 'nominal' => '700000.00', 'keterangan' => 'SALDO PIUTANG PER TGL 01-05-2023', 'invoice_nobukti' => 'INV 0008/IV/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '11', 'nobukti' => 'EPT 0009/IV/2023', 'nominal' => '2862500.00', 'keterangan' => 'SALDO PIUTANG PER TGL 01-05-2023', 'invoice_nobukti' => 'INV 0009/IV/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '12', 'nobukti' => 'EPT 0010/IV/2023', 'nominal' => '1500000.00', 'keterangan' => 'SALDO PIUTANG PER TGL 01-05-2023', 'invoice_nobukti' => 'INV 0010/IV/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '13', 'nobukti' => 'EPT 0011/IV/2023', 'nominal' => '1019000.00', 'keterangan' => 'SALDO PIUTANG PER TGL 01-05-2023', 'invoice_nobukti' => 'INV 0011/IV/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '14', 'nobukti' => 'EPT 0012/IV/2023', 'nominal' => '34442250.00', 'keterangan' => 'SALDO PIUTANG PER TGL 01-05-2023', 'invoice_nobukti' => 'INV 0012/IV/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '15', 'nobukti' => 'EPT 0013/IV/2023', 'nominal' => '10893750.00', 'keterangan' => 'SALDO PIUTANG PER TGL 01-05-2023', 'invoice_nobukti' => 'INV 0013/IV/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '16', 'nobukti' => 'EPT 0014/IV/2023', 'nominal' => '2500000.00', 'keterangan' => 'SALDO PIUTANG PER TGL 01-05-2023', 'invoice_nobukti' => 'INV 0014/IV/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '17', 'nobukti' => 'EPT 0015/IV/2023', 'nominal' => '30647250.00', 'keterangan' => 'SALDO PIUTANG PER TGL 01-05-2023', 'invoice_nobukti' => 'INV 0015/IV/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '18', 'nobukti' => 'EPT 0016/IV/2023', 'nominal' => '24000000.00', 'keterangan' => 'SALDO PIUTANG PER TGL 01-05-2023', 'invoice_nobukti' => 'INV 0016/IV/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '19', 'nobukti' => 'EPT 0017/IV/2023', 'nominal' => '4173500.00', 'keterangan' => 'SALDO PIUTANG PER TGL 01-05-2023', 'invoice_nobukti' => 'INV 0017/IV/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '20', 'nobukti' => 'EPT 0018/IV/2023', 'nominal' => '16800000.00', 'keterangan' => 'SALDO PIUTANG PER TGL 01-05-2023', 'invoice_nobukti' => 'INV 0018/IV/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '21', 'nobukti' => 'EPT 0019/IV/2023', 'nominal' => '5000000.00', 'keterangan' => 'SALDO PIUTANG PER TGL 01-05-2023', 'invoice_nobukti' => 'INV 0019/IV/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '22', 'nobukti' => 'EPT 0020/IV/2023', 'nominal' => '3218500.00', 'keterangan' => 'SALDO PIUTANG PER TGL 01-05-2023', 'invoice_nobukti' => 'INV 0020/IV/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '23', 'nobukti' => 'EPT 0021/IV/2023', 'nominal' => '4718500.00', 'keterangan' => 'SALDO PIUTANG PER TGL 01-05-2023', 'invoice_nobukti' => 'INV 0021/IV/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '24', 'nobukti' => 'EPT 0022/IV/2023', 'nominal' => '2400000.00', 'keterangan' => 'SALDO PIUTANG PER TGL 01-05-2023', 'invoice_nobukti' => 'INV 0022/IV/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '25', 'nobukti' => 'EPT 0023/II/2023', 'nominal' => '8057500.00', 'keterangan' => 'SALDO PIUTANG PER TGL 01-05-2023', 'invoice_nobukti' => 'INV 0023/II/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '26', 'nobukti' => 'EPT 0023/III/2023', 'nominal' => '18005000.00', 'keterangan' => 'SALDO PIUTANG PER TGL 01-05-2023', 'invoice_nobukti' => 'INV 0023/III/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '27', 'nobukti' => 'EPT 0023/IV/2023', 'nominal' => '8092500.00', 'keterangan' => 'SALDO PIUTANG PER TGL 01-05-2023', 'invoice_nobukti' => 'INV 0023/IV/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '28', 'nobukti' => 'EPT 0024/IV/2023', 'nominal' => '7864000.00', 'keterangan' => 'SALDO PIUTANG PER TGL 01-05-2023', 'invoice_nobukti' => 'INV 0024/IV/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '29', 'nobukti' => 'EPT 0025/IV/2023', 'nominal' => '5447000.00', 'keterangan' => 'SALDO PIUTANG PER TGL 01-05-2023', 'invoice_nobukti' => 'INV 0025/IV/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '30', 'nobukti' => 'EPT 0026/IV/2023', 'nominal' => '1785000.00', 'keterangan' => 'SALDO PIUTANG PER TGL 01-05-2023', 'invoice_nobukti' => 'INV 0026/IV/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '31', 'nobukti' => 'EPT 0027/IV/2023', 'nominal' => '9663500.00', 'keterangan' => 'SALDO PIUTANG PER TGL 01-05-2023', 'invoice_nobukti' => 'INV 0027/IV/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '32', 'nobukti' => 'EPT 0028/III/2023', 'nominal' => '820000.00', 'keterangan' => 'SALDO PIUTANG PER TGL 01-05-2023', 'invoice_nobukti' => 'INV 0028/III/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '33', 'nobukti' => 'EPT 0028/IV/2023', 'nominal' => '8790000.00', 'keterangan' => 'SALDO PIUTANG PER TGL 01-05-2023', 'invoice_nobukti' => 'INV 0028/IV/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '34', 'nobukti' => 'EPT 0029/IV/2023', 'nominal' => '2460000.00', 'keterangan' => 'SALDO PIUTANG PER TGL 01-05-2023', 'invoice_nobukti' => 'INV 0029/IV/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '35', 'nobukti' => 'EPT 0030/IV/2023', 'nominal' => '9143750.00', 'keterangan' => 'SALDO PIUTANG PER TGL 01-05-2023', 'invoice_nobukti' => 'INV 0030/IV/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '36', 'nobukti' => 'EPT 0032/II/2023', 'nominal' => '4005000.00', 'keterangan' => 'SALDO PIUTANG PER TGL 01-05-2023', 'invoice_nobukti' => 'INV 0032/II/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '37', 'nobukti' => 'EPT 0034/III/2023', 'nominal' => '33885000.00', 'keterangan' => 'SALDO PIUTANG PER TGL 01-05-2023', 'invoice_nobukti' => 'INV 0034/III/2023', 'modifiedby' => 'CHAIRUNNISA',]);
        piutangdetail::create(['piutang_id' => '38', 'nobukti' => 'EPT 0035/III/2023', 'nominal' => '5009000.00', 'keterangan' => 'SALDO PIUTANG PER TGL 01-05-2023', 'invoice_nobukti' => 'INV 0035/III/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '39', 'nobukti' => 'EPT 0036/III/2023', 'nominal' => '12000000.00', 'keterangan' => 'SALDO PIUTANG PER TGL 01-05-2023', 'invoice_nobukti' => 'INV 0036/III/2023', 'modifiedby' => 'CHAIRUNNISA',]);
        piutangdetail::create(['piutang_id' => '40', 'nobukti' => 'EPT 0037/II/2023', 'nominal' => '1431250.00', 'keterangan' => 'SALDO PIUTANG PER TGL 01-05-2023', 'invoice_nobukti' => 'INV 0037/II/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '41', 'nobukti' => 'EPT 0037/III/2023', 'nominal' => '5099500.00', 'keterangan' => 'SALDO PIUTANG PER TGL 01-05-2023', 'invoice_nobukti' => 'INV 0037/III/2023', 'modifiedby' => 'CHAIRUNNISA',]);
        piutangdetail::create(['piutang_id' => '42', 'nobukti' => 'EPT 0038/III/2023', 'nominal' => '6326000.00', 'keterangan' => 'SALDO PIUTANG PER TGL 01-05-2023', 'invoice_nobukti' => 'INV 0038/III/2023', 'modifiedby' => 'CHAIRUNNISA',]);
        piutangdetail::create(['piutang_id' => '43', 'nobukti' => 'EPT 0040/III/2023', 'nominal' => '1830000.00', 'keterangan' => 'SALDO PIUTANG PER TGL 01-05-2023', 'invoice_nobukti' => 'INV 0040/III/2023', 'modifiedby' => 'CHAIRUNNISA',]);
    }
}
