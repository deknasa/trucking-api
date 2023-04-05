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


        piutangdetail::create(['piutang_id' => '1', 'nobukti' => 'EPT 0001/I/2023', 'nominal' => '7200000', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0001/I/2023', 'modifiedby' => 'CHAIRUNNISA',]);
        piutangdetail::create(['piutang_id' => '2', 'nobukti' => 'EPT 0002/I/2023', 'nominal' => '30052250', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0002/I/2023', 'modifiedby' => 'CHAIRUNNISA',]);
        piutangdetail::create(['piutang_id' => '3', 'nobukti' => 'EPT 0003/I/2023', 'nominal' => '4692500', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0003/I/2023', 'modifiedby' => 'CHAIRUNNISA',]);
        piutangdetail::create(['piutang_id' => '4', 'nobukti' => 'EPT 0004/I/2023', 'nominal' => '8505250', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0004/I/2023', 'modifiedby' => 'CHAIRUNNISA',]);
        piutangdetail::create(['piutang_id' => '5', 'nobukti' => 'EPT 0005/I/2023', 'nominal' => '700000', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0005/I/2023', 'modifiedby' => 'CHAIRUNNISA',]);
        piutangdetail::create(['piutang_id' => '6', 'nobukti' => 'EPT 0007/I/2023', 'nominal' => '300000', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0007/I/2023', 'modifiedby' => 'CHAIRUNNISA',]);
        piutangdetail::create(['piutang_id' => '7', 'nobukti' => 'EPT 0007/XII/2022', 'nominal' => '8700000', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0007/XII/2022', 'modifiedby' => 'CHAIRUNNISA',]);
        piutangdetail::create(['piutang_id' => '8', 'nobukti' => 'EPT 0008/I/2023', 'nominal' => '32662750', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0008/I/2023', 'modifiedby' => 'CHAIRUNNISA',]);
        piutangdetail::create(['piutang_id' => '9', 'nobukti' => 'EPT 0009/I/2023', 'nominal' => '2552500', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0009/I/2023', 'modifiedby' => 'CHAIRUNNISA',]);
        piutangdetail::create(['piutang_id' => '10', 'nobukti' => 'EPT 0010/I/2023', 'nominal' => '5135000', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0010/I/2023', 'modifiedby' => 'CHAIRUNNISA',]);
        piutangdetail::create(['piutang_id' => '11', 'nobukti' => 'EPT 0011/I/2023', 'nominal' => '24531250', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0011/I/2023', 'modifiedby' => 'CHAIRUNNISA',]);
        piutangdetail::create(['piutang_id' => '12', 'nobukti' => 'EPT 0012/I/2023', 'nominal' => '5250000', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0012/I/2023', 'modifiedby' => 'CHAIRUNNISA',]);
        piutangdetail::create(['piutang_id' => '13', 'nobukti' => 'EPT 0013/I/2023', 'nominal' => '15832000', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0013/I/2023', 'modifiedby' => 'CHAIRUNNISA',]);
        piutangdetail::create(['piutang_id' => '14', 'nobukti' => 'EPT 0014/I/2023', 'nominal' => '16800000', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0014/I/2023', 'modifiedby' => 'CHAIRUNNISA',]);
        piutangdetail::create(['piutang_id' => '15', 'nobukti' => 'EPT 0014/XII/2022', 'nominal' => '9900000', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0014/XII/2022', 'modifiedby' => 'CHAIRUNNISA',]);
        piutangdetail::create(['piutang_id' => '16', 'nobukti' => 'EPT 0015/I/2023', 'nominal' => '600000', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0015/I/2023', 'modifiedby' => 'CHAIRUNNISA',]);
        piutangdetail::create(['piutang_id' => '17', 'nobukti' => 'EPT 0015/XII/2022', 'nominal' => '5100000', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0015/XII/2022', 'modifiedby' => 'CHAIRUNNISA',]);
        piutangdetail::create(['piutang_id' => '18', 'nobukti' => 'EPT 0016/I/2023', 'nominal' => '49023750', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0016/I/2023', 'modifiedby' => 'AYEN',]);
        piutangdetail::create(['piutang_id' => '19', 'nobukti' => 'EPT 0017/I/2023', 'nominal' => '10941500', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0017/I/2023', 'modifiedby' => 'CHAIRUNNISA',]);
        piutangdetail::create(['piutang_id' => '20', 'nobukti' => 'EPT 0018/I/2023', 'nominal' => '1750000', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0018/I/2023', 'modifiedby' => 'CHAIRUNNISA',]);
        piutangdetail::create(['piutang_id' => '21', 'nobukti' => 'EPT 0019/I/2023', 'nominal' => '26827750', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0019/I/2023', 'modifiedby' => 'CHAIRUNNISA',]);
        piutangdetail::create(['piutang_id' => '22', 'nobukti' => 'EPT 0020/I/2023', 'nominal' => '6232000', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0020/I/2023', 'modifiedby' => 'CHAIRUNNISA',]);
        piutangdetail::create(['piutang_id' => '23', 'nobukti' => 'EPT 0021/I/2023', 'nominal' => '7664000', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0021/I/2023', 'modifiedby' => 'CHAIRUNNISA',]);
        piutangdetail::create(['piutang_id' => '24', 'nobukti' => 'EPT 0022/I/2023', 'nominal' => '1431250', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0022/I/2023', 'modifiedby' => 'CHAIRUNNISA',]);
        piutangdetail::create(['piutang_id' => '25', 'nobukti' => 'EPT 0023/I/2023', 'nominal' => '51857500', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0023/I/2023', 'modifiedby' => 'CHAIRUNNISA',]);
        piutangdetail::create(['piutang_id' => '26', 'nobukti' => 'EPT 0023/XII/2022', 'nominal' => '15600000', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0023/XII/2022', 'modifiedby' => 'CHAIRUNNISA',]);
        piutangdetail::create(['piutang_id' => '27', 'nobukti' => 'EPT 0024/I/2023', 'nominal' => '6232000', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0024/I/2023', 'modifiedby' => 'CHAIRUNNISA',]);
        piutangdetail::create(['piutang_id' => '28', 'nobukti' => 'EPT 0025/I/2023', 'nominal' => '10886000', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0025/I/2023', 'modifiedby' => 'CHAIRUNNISA',]);
        piutangdetail::create(['piutang_id' => '29', 'nobukti' => 'EPT 0025/XII/2022', 'nominal' => '3900000', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0025/XII/2022', 'modifiedby' => 'CHAIRUNNISA',]);
        piutangdetail::create(['piutang_id' => '30', 'nobukti' => 'EPT 0026/I/2023', 'nominal' => '17837250', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0026/I/2023', 'modifiedby' => 'CHAIRUNNISA',]);
        piutangdetail::create(['piutang_id' => '31', 'nobukti' => 'EPT 0026/XII/2022', 'nominal' => '3000000', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0026/XII/2022', 'modifiedby' => 'CHAIRUNNISA',]);
        piutangdetail::create(['piutang_id' => '32', 'nobukti' => 'EPT 0027/I/2023', 'nominal' => '2625000', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0027/I/2023', 'modifiedby' => 'CHAIRUNNISA',]);
        piutangdetail::create(['piutang_id' => '33', 'nobukti' => 'EPT 0028/I/2023', 'nominal' => '3500000', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0028/I/2023', 'modifiedby' => 'CHAIRUNNISA',]);
        piutangdetail::create(['piutang_id' => '34', 'nobukti' => 'EPT 0029/I/2023', 'nominal' => '2625000', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0029/I/2023', 'modifiedby' => 'CHAIRUNNISA',]);
        piutangdetail::create(['piutang_id' => '35', 'nobukti' => 'EPT 0030/I/2023', 'nominal' => '2625000', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0030/I/2023', 'modifiedby' => 'CHAIRUNNISA',]);
        piutangdetail::create(['piutang_id' => '36', 'nobukti' => 'EPT 0031/I/2023', 'nominal' => '2625000', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0031/I/2023', 'modifiedby' => 'CHAIRUNNISA',]);
        piutangdetail::create(['piutang_id' => '37', 'nobukti' => 'EPT 0031/XI/2022', 'nominal' => '5700000', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0031/XI/2022', 'modifiedby' => 'CHAIRUNNISA',]);
        piutangdetail::create(['piutang_id' => '38', 'nobukti' => 'EPT 0032/I/2023', 'nominal' => '1800000', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0032/I/2023', 'modifiedby' => 'CHAIRUNNISA',]);
        piutangdetail::create(['piutang_id' => '39', 'nobukti' => 'EPT 0032/XII/2022', 'nominal' => '1500000', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0032/XII/2022', 'modifiedby' => 'CHAIRUNNISA',]);
        piutangdetail::create(['piutang_id' => '40', 'nobukti' => 'EPT 0033/I/2023', 'nominal' => '44957000', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0033/I/2023', 'modifiedby' => 'CHAIRUNNISA',]);
        piutangdetail::create(['piutang_id' => '41', 'nobukti' => 'EPT 0034/I/2023', 'nominal' => '8298750', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0034/I/2023', 'modifiedby' => 'CHAIRUNNISA',]);
        piutangdetail::create(['piutang_id' => '42', 'nobukti' => 'EPT 0035/I/2023', 'nominal' => '17740000', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0035/I/2023', 'modifiedby' => 'CHAIRUNNISA',]);
        piutangdetail::create(['piutang_id' => '43', 'nobukti' => 'EPT 0036/I/2023', 'nominal' => '8235250', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0036/I/2023', 'modifiedby' => 'CHAIRUNNISA',]);
        piutangdetail::create(['piutang_id' => '44', 'nobukti' => 'EPT 0037/I/2023', 'nominal' => '6060750', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0037/I/2023', 'modifiedby' => 'CHAIRUNNISA',]);
        piutangdetail::create(['piutang_id' => '45', 'nobukti' => 'EPT 0038/I/2023', 'nominal' => '4866500', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0038/I/2023', 'modifiedby' => 'CHAIRUNNISA',]);
        piutangdetail::create(['piutang_id' => '46', 'nobukti' => 'EPT 0039/I/2023', 'nominal' => '446250', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0039/I/2023', 'modifiedby' => 'CHAIRUNNISA',]);
        piutangdetail::create(['piutang_id' => '47', 'nobukti' => 'EPT 0040/I/2023', 'nominal' => '446250', 'keterangan' => 'SALDO PIUTANG PER TGL 01-02-2023', 'invoice_nobukti' => 'INV 0040/I/2023', 'modifiedby' => 'CHAIRUNNISA',]);
        piutangdetail::create(['piutang_id' => '48', 'nobukti' => 'EPT 0001/II/2023', 'nominal' => '1431250', 'keterangan' => '', 'invoice_nobukti' => 'INV 0001/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '48', 'nobukti' => 'EPT 0001/II/2023', 'nominal' => '1277000', 'keterangan' => '', 'invoice_nobukti' => 'INV 0001/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '48', 'nobukti' => 'EPT 0001/II/2023', 'nominal' => '1277000', 'keterangan' => '', 'invoice_nobukti' => 'INV 0001/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '48', 'nobukti' => 'EPT 0001/II/2023', 'nominal' => '1431250', 'keterangan' => '', 'invoice_nobukti' => 'INV 0001/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '48', 'nobukti' => 'EPT 0001/II/2023', 'nominal' => '2400000', 'keterangan' => '', 'invoice_nobukti' => 'INV 0001/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '48', 'nobukti' => 'EPT 0001/II/2023', 'nominal' => '2400000', 'keterangan' => '', 'invoice_nobukti' => 'INV 0001/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '48', 'nobukti' => 'EPT 0001/II/2023', 'nominal' => '1277000', 'keterangan' => '', 'invoice_nobukti' => 'INV 0001/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '48', 'nobukti' => 'EPT 0001/II/2023', 'nominal' => '1432000', 'keterangan' => '', 'invoice_nobukti' => 'INV 0001/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '48', 'nobukti' => 'EPT 0001/II/2023', 'nominal' => '1277000', 'keterangan' => '', 'invoice_nobukti' => 'INV 0001/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '48', 'nobukti' => 'EPT 0001/II/2023', 'nominal' => '1431250', 'keterangan' => '', 'invoice_nobukti' => 'INV 0001/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '48', 'nobukti' => 'EPT 0001/II/2023', 'nominal' => '1431250', 'keterangan' => '', 'invoice_nobukti' => 'INV 0001/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '48', 'nobukti' => 'EPT 0001/II/2023', 'nominal' => '2400000', 'keterangan' => '', 'invoice_nobukti' => 'INV 0001/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '48', 'nobukti' => 'EPT 0001/II/2023', 'nominal' => '1830000', 'keterangan' => '', 'invoice_nobukti' => 'INV 0001/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '48', 'nobukti' => 'EPT 0001/II/2023', 'nominal' => '1277000', 'keterangan' => '', 'invoice_nobukti' => 'INV 0001/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '48', 'nobukti' => 'EPT 0001/II/2023', 'nominal' => '2400000', 'keterangan' => '', 'invoice_nobukti' => 'INV 0001/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '48', 'nobukti' => 'EPT 0001/II/2023', 'nominal' => '1117000', 'keterangan' => '', 'invoice_nobukti' => 'INV 0001/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '48', 'nobukti' => 'EPT 0001/II/2023', 'nominal' => '1432000', 'keterangan' => '', 'invoice_nobukti' => 'INV 0001/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '48', 'nobukti' => 'EPT 0001/II/2023', 'nominal' => '892500', 'keterangan' => '', 'invoice_nobukti' => 'INV 0001/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '48', 'nobukti' => 'EPT 0001/II/2023', 'nominal' => '1117000', 'keterangan' => '', 'invoice_nobukti' => 'INV 0001/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '48', 'nobukti' => 'EPT 0001/II/2023', 'nominal' => '1277000', 'keterangan' => '', 'invoice_nobukti' => 'INV 0001/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '48', 'nobukti' => 'EPT 0001/II/2023', 'nominal' => '2400000', 'keterangan' => '', 'invoice_nobukti' => 'INV 0001/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '48', 'nobukti' => 'EPT 0001/II/2023', 'nominal' => '2400000', 'keterangan' => '', 'invoice_nobukti' => 'INV 0001/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '48', 'nobukti' => 'EPT 0001/II/2023', 'nominal' => '892500', 'keterangan' => '', 'invoice_nobukti' => 'INV 0001/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '48', 'nobukti' => 'EPT 0001/II/2023', 'nominal' => '1432000', 'keterangan' => '', 'invoice_nobukti' => 'INV 0001/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '48', 'nobukti' => 'EPT 0001/II/2023', 'nominal' => '892500', 'keterangan' => '', 'invoice_nobukti' => 'INV 0001/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '48', 'nobukti' => 'EPT 0001/II/2023', 'nominal' => '1277000', 'keterangan' => '', 'invoice_nobukti' => 'INV 0001/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '48', 'nobukti' => 'EPT 0001/II/2023', 'nominal' => '1431250', 'keterangan' => '', 'invoice_nobukti' => 'INV 0001/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '48', 'nobukti' => 'EPT 0001/II/2023', 'nominal' => '1431250', 'keterangan' => '', 'invoice_nobukti' => 'INV 0001/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '48', 'nobukti' => 'EPT 0001/II/2023', 'nominal' => '1431250', 'keterangan' => '', 'invoice_nobukti' => 'INV 0001/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '48', 'nobukti' => 'EPT 0001/II/2023', 'nominal' => '1277000', 'keterangan' => '', 'invoice_nobukti' => 'INV 0001/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '48', 'nobukti' => 'EPT 0001/II/2023', 'nominal' => '892500', 'keterangan' => '', 'invoice_nobukti' => 'INV 0001/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '48', 'nobukti' => 'EPT 0001/II/2023', 'nominal' => '2250000', 'keterangan' => '', 'invoice_nobukti' => 'INV 0001/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '49', 'nobukti' => 'EPT 0002/II/2023', 'nominal' => '5023000', 'keterangan' => 'TAGIHAN INVOICE BONGKARAN TAS AP PERIODE 01-02-2023 S/D 02-02-2023', 'invoice_nobukti' => 'INV 0002/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '50', 'nobukti' => 'EPT 0003/II/2023', 'nominal' => '5023000', 'keterangan' => '', 'invoice_nobukti' => 'INV 0003/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '51', 'nobukti' => 'EPT 0004/II/2023', 'nominal' => '6232000', 'keterangan' => '', 'invoice_nobukti' => 'INV 0004/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '52', 'nobukti' => 'EPT 0005/II/2023', 'nominal' => '2625000', 'keterangan' => '', 'invoice_nobukti' => 'INV 0005/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '53', 'nobukti' => 'EPT 0006/II/2023', 'nominal' => '21858000', 'keterangan' => '', 'invoice_nobukti' => 'INV 0006/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '54', 'nobukti' => 'EPT 0007/II/2023', 'nominal' => '3115000', 'keterangan' => '', 'invoice_nobukti' => 'INV 0007/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '55', 'nobukti' => 'EPT 0008/II/2023', 'nominal' => '700000', 'keterangan' => '', 'invoice_nobukti' => 'INV 0008/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '56', 'nobukti' => 'EPT 0009/II/2023', 'nominal' => '3000000', 'keterangan' => '', 'invoice_nobukti' => 'INV 0009/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '57', 'nobukti' => 'EPT 0010/II/2023', 'nominal' => '12266667', 'keterangan' => 'BIAYA SEWA UNIT UNTUK PT WIRATAMA GROUP BK 9418 LO DAN BK 8007 XA SELAMA 8 HARI @766.666,66 ', 'invoice_nobukti' => 'INV 0010/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '58', 'nobukti' => 'EPT 0011/II/2023', 'nominal' => '10953000', 'keterangan' => '', 'invoice_nobukti' => 'INV 0011/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '59', 'nobukti' => 'EPT 0012/II/2023', 'nominal' => '37010000', 'keterangan' => '', 'invoice_nobukti' => 'INV 0012/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '60', 'nobukti' => 'EPT 0013/II/2023', 'nominal' => '22064000', 'keterangan' => '', 'invoice_nobukti' => 'INV 0013/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '61', 'nobukti' => 'EPT 0014/II/2023', 'nominal' => '8632000', 'keterangan' => '', 'invoice_nobukti' => 'INV 0014/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '62', 'nobukti' => 'EPT 0015/II/2023', 'nominal' => '61664250', 'keterangan' => '', 'invoice_nobukti' => 'INV 0015/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '63', 'nobukti' => 'EPT 0016/II/2023', 'nominal' => '2625000', 'keterangan' => '', 'invoice_nobukti' => 'INV 0016/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '64', 'nobukti' => 'EPT 0019/II/2023', 'nominal' => '55594000', 'keterangan' => '', 'invoice_nobukti' => 'INV 0019/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '65', 'nobukti' => 'EPT 0020/II/2023', 'nominal' => '15832000', 'keterangan' => '', 'invoice_nobukti' => 'INV 0020/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '66', 'nobukti' => 'EPT 0021/II/2023', 'nominal' => '2862500', 'keterangan' => '', 'invoice_nobukti' => 'INV 0021/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '67', 'nobukti' => 'EPT 0022/II/2023', 'nominal' => '25528500', 'keterangan' => '', 'invoice_nobukti' => 'INV 0022/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '68', 'nobukti' => 'EPT 0023/II/2023', 'nominal' => '8057500', 'keterangan' => '', 'invoice_nobukti' => 'INV 0023/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '69', 'nobukti' => 'EPT 0024/II/2023', 'nominal' => '4800000', 'keterangan' => '', 'invoice_nobukti' => 'INV 0024/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '70', 'nobukti' => 'EPT 0025/II/2023', 'nominal' => '1915500', 'keterangan' => '', 'invoice_nobukti' => 'INV 0025/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '71', 'nobukti' => 'EPT 0026/II/2023', 'nominal' => '46542000', 'keterangan' => '', 'invoice_nobukti' => 'INV 0026/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '72', 'nobukti' => 'EPT 0027/II/2023', 'nominal' => '30702250', 'keterangan' => '', 'invoice_nobukti' => 'INV 0027/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '73', 'nobukti' => 'EPT 0028/II/2023', 'nominal' => '4092500', 'keterangan' => '', 'invoice_nobukti' => 'INV 0028/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '74', 'nobukti' => 'EPT 0029/II/2023', 'nominal' => '2625000', 'keterangan' => '', 'invoice_nobukti' => 'INV 0029/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '75', 'nobukti' => 'EPT 0030/II/2023', 'nominal' => '2625000', 'keterangan' => '', 'invoice_nobukti' => 'INV 0030/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '76', 'nobukti' => 'EPT 0031/II/2023', 'nominal' => '2400000', 'keterangan' => '', 'invoice_nobukti' => 'INV 0031/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '77', 'nobukti' => 'EPT 0032/II/2023', 'nominal' => '4005000', 'keterangan' => '', 'invoice_nobukti' => 'INV 0032/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '78', 'nobukti' => 'EPT 0033/II/2023', 'nominal' => '26557250', 'keterangan' => '', 'invoice_nobukti' => 'INV 0033/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '79', 'nobukti' => 'EPT 0034/II/2023', 'nominal' => '7200000', 'keterangan' => '', 'invoice_nobukti' => 'INV 0034/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '80', 'nobukti' => 'EPT 0035/II/2023', 'nominal' => '3107000', 'keterangan' => '', 'invoice_nobukti' => 'INV 0035/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '81', 'nobukti' => 'EPT 0036/II/2023', 'nominal' => '13231750', 'keterangan' => '', 'invoice_nobukti' => 'INV 0036/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '82', 'nobukti' => 'EPT 0037/II/2023', 'nominal' => '1431250', 'keterangan' => '', 'invoice_nobukti' => 'INV 0037/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '83', 'nobukti' => 'EPT 0038/II/2023', 'nominal' => '36898500', 'keterangan' => '', 'invoice_nobukti' => 'INV 0038/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '84', 'nobukti' => 'EPT 0039/II/2023', 'nominal' => '13883500', 'keterangan' => '', 'invoice_nobukti' => 'INV 0039/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '85', 'nobukti' => 'EPT 0040/II/2023', 'nominal' => '22931500', 'keterangan' => '', 'invoice_nobukti' => 'INV 0040/II/2023', 'modifiedby' => 'ADMIN',]);
        piutangdetail::create(['piutang_id' => '86', 'nobukti' => 'EPT 0041/II/2023', 'nominal' => '1800000', 'keterangan' => '', 'invoice_nobukti' => 'INV 0041/II/2023', 'modifiedby' => 'ADMIN',]);
    }
}
