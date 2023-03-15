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

        piutangdetail::create(['piutang_id' => '1', 'nobukti' => 'EPT 0001/I/2023', 'nominal' => '7200000', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0001/I/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '2', 'nobukti' => 'EPT 0002/I/2023', 'nominal' => '30052250', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0002/I/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '3', 'nobukti' => 'EPT 0003/I/2023', 'nominal' => '4692500', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0003/I/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '4', 'nobukti' => 'EPT 0004/I/2023', 'nominal' => '8505250', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0004/I/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '5', 'nobukti' => 'EPT 0005/I/2023', 'nominal' => '700000', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0005/I/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '6', 'nobukti' => 'EPT 0007/I/2023', 'nominal' => '300000', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0007/I/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '7', 'nobukti' => 'EPT 0007/XII/2022', 'nominal' => '8700000', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0007/XII/2022', 'modifiedby' => 'CHAIRUNNISA',]);
        piutangdetail::create(['piutang_id' => '8', 'nobukti' => 'EPT 0008/I/2023', 'nominal' => '32662750', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0008/I/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '9', 'nobukti' => 'EPT 0009/I/2023', 'nominal' => '2552500', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0009/I/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '10', 'nobukti' => 'EPT 0010/I/2023', 'nominal' => '5135000', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0010/I/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '11', 'nobukti' => 'EPT 0011/I/2023', 'nominal' => '24531250', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0011/I/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '12', 'nobukti' => 'EPT 0012/I/2023', 'nominal' => '5250000', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0012/I/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '13', 'nobukti' => 'EPT 0013/I/2023', 'nominal' => '15832000', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0013/I/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '14', 'nobukti' => 'EPT 0014/I/2023', 'nominal' => '16800000', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0014/I/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '15', 'nobukti' => 'EPT 0014/XII/2022', 'nominal' => '9900000', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0014/XII/2022', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '16', 'nobukti' => 'EPT 0015/I/2023', 'nominal' => '600000', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0015/I/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '17', 'nobukti' => 'EPT 0015/XII/2022', 'nominal' => '5100000', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0015/XII/2022', 'modifiedby' => 'CHAIRUNNISA',]);
        piutangdetail::create(['piutang_id' => '18', 'nobukti' => 'EPT 0016/I/2023', 'nominal' => '49023750', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0016/I/2023', 'modifiedby' => 'AYEN',]);
        piutangdetail::create(['piutang_id' => '19', 'nobukti' => 'EPT 0017/I/2023', 'nominal' => '10941500', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0017/I/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '20', 'nobukti' => 'EPT 0018/I/2023', 'nominal' => '1750000', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0018/I/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '21', 'nobukti' => 'EPT 0019/I/2023', 'nominal' => '26827750', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0019/I/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '22', 'nobukti' => 'EPT 0020/I/2023', 'nominal' => '6232000', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0020/I/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '23', 'nobukti' => 'EPT 0021/I/2023', 'nominal' => '7664000', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0021/I/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '24', 'nobukti' => 'EPT 0022/I/2023', 'nominal' => '1431250', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0022/I/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '25', 'nobukti' => 'EPT 0023/I/2023', 'nominal' => '51857500', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0023/I/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '26', 'nobukti' => 'EPT 0023/XII/2022', 'nominal' => '15600000', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0023/XII/2022', 'modifiedby' => 'CHAIRUNNISA',]);
        piutangdetail::create(['piutang_id' => '27', 'nobukti' => 'EPT 0024/I/2023', 'nominal' => '6232000', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0024/I/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '28', 'nobukti' => 'EPT 0025/I/2023', 'nominal' => '10886000', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0025/I/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '29', 'nobukti' => 'EPT 0025/XII/2022', 'nominal' => '3900000', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0025/XII/2022', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '30', 'nobukti' => 'EPT 0026/I/2023', 'nominal' => '17837250', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0026/I/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '31', 'nobukti' => 'EPT 0026/XII/2022', 'nominal' => '3000000', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0026/XII/2022', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '32', 'nobukti' => 'EPT 0027/I/2023', 'nominal' => '2625000', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0027/I/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '33', 'nobukti' => 'EPT 0028/I/2023', 'nominal' => '3500000', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0028/I/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '34', 'nobukti' => 'EPT 0029/I/2023', 'nominal' => '2625000', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0029/I/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '35', 'nobukti' => 'EPT 0030/I/2023', 'nominal' => '2625000', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0030/I/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '36', 'nobukti' => 'EPT 0031/I/2023', 'nominal' => '2625000', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0031/I/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '37', 'nobukti' => 'EPT 0031/XI/2022', 'nominal' => '5700000', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0031/XI/2022', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '38', 'nobukti' => 'EPT 0032/I/2023', 'nominal' => '1800000', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0032/I/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '39', 'nobukti' => 'EPT 0032/XII/2022', 'nominal' => '1500000', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0032/XII/2022', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '40', 'nobukti' => 'EPT 0033/I/2023', 'nominal' => '44957000', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0033/I/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '41', 'nobukti' => 'EPT 0034/I/2023', 'nominal' => '8298750', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0034/I/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '42', 'nobukti' => 'EPT 0035/I/2023', 'nominal' => '17740000', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0035/I/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '43', 'nobukti' => 'EPT 0036/I/2023', 'nominal' => '8235250', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0036/I/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '44', 'nobukti' => 'EPT 0037/I/2023', 'nominal' => '6060750', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0037/I/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '45', 'nobukti' => 'EPT 0038/I/2023', 'nominal' => '4866500', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0038/I/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '46', 'nobukti' => 'EPT 0039/I/2023', 'nominal' => '446250', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0039/I/2023', 'modifiedby' => 'chairunnisa',]);
        piutangdetail::create(['piutang_id' => '47', 'nobukti' => 'EPT 0040/I/2023', 'nominal' => '446250', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0040/I/2023', 'modifiedby' => 'chairunnisa',]);
    }
}
