<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InvoiceHeader;
use Illuminate\Support\Facades\DB;

class InvoiceHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete invoiceheader");
        DB::statement("DBCC CHECKIDENT ('invoiceheader', RESEED, 1);");

        invoiceheader::create(['nobukti' => 'INV 0001/II/2023', 'tglbukti' => '2023/2/10', 'keterangan' => '', 'nominal' => '48814750', 'tglterima' => '2023/2/10', 'tgljatuhtempo' => '2023/3/18', 'agen_id' => '64', 'jenisorder_id' => '2', 'cabang_id' => '0', 'piutang_nobukti' => 'EPT 0001/II/2023', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'tgldari' => '2023/2/1', 'tglsampai' => '2023/2/10', 'statusformat' => '151', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
        invoiceheader::create(['nobukti' => 'INV 0002/II/2023', 'tglbukti' => '2023/2/10', 'keterangan' => '', 'nominal' => '5023000', 'tglterima' => '2023/2/10', 'tgljatuhtempo' => '2023/4/5', 'agen_id' => '64', 'jenisorder_id' => '2', 'cabang_id' => '0', 'piutang_nobukti' => 'EPT 0002/II/2023', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'tgldari' => '2023/2/1', 'tglsampai' => '2023/2/2', 'statusformat' => '151', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
        invoiceheader::create(['nobukti' => 'INV 0003/II/2023', 'tglbukti' => '2023/2/10', 'keterangan' => '', 'nominal' => '5023000', 'tglterima' => '2023/2/10', 'tgljatuhtempo' => '2023/2/10', 'agen_id' => '64', 'jenisorder_id' => '2', 'cabang_id' => '2', 'piutang_nobukti' => 'EPT 0003/II/2023', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'tgldari' => '2023/2/1', 'tglsampai' => '2023/2/10', 'statusformat' => '151', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
        invoiceheader::create(['nobukti' => 'INV 0004/II/2023', 'tglbukti' => '2023/2/10', 'keterangan' => '', 'nominal' => '6232000', 'tglterima' => '2023/2/10', 'tgljatuhtempo' => '2023/2/10', 'agen_id' => '64', 'jenisorder_id' => '2', 'cabang_id' => '2', 'piutang_nobukti' => 'EPT 0004/II/2023', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'tgldari' => '2023/2/1', 'tglsampai' => '2023/2/10', 'statusformat' => '151', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
        invoiceheader::create(['nobukti' => 'INV 0005/II/2023', 'tglbukti' => '2023/2/10', 'keterangan' => '', 'nominal' => '2625000', 'tglterima' => '2023/2/10', 'tgljatuhtempo' => '2023/2/10', 'agen_id' => '64', 'jenisorder_id' => '1', 'cabang_id' => '2', 'piutang_nobukti' => 'EPT 0005/II/2023', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'tgldari' => '2023/2/1', 'tglsampai' => '2023/2/10', 'statusformat' => '151', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
        invoiceheader::create(['nobukti' => 'INV 0006/II/2023', 'tglbukti' => '2023/2/10', 'keterangan' => '', 'nominal' => '21858000', 'tglterima' => '2023/2/10', 'tgljatuhtempo' => '2023/2/10', 'agen_id' => '64', 'jenisorder_id' => '1', 'cabang_id' => '2', 'piutang_nobukti' => 'EPT 0006/II/2023', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'tgldari' => '2023/2/1', 'tglsampai' => '2023/2/10', 'statusformat' => '151', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
        invoiceheader::create(['nobukti' => 'INV 0007/II/2023', 'tglbukti' => '2023/2/10', 'keterangan' => '', 'nominal' => '3115000', 'tglterima' => '2023/2/10', 'tgljatuhtempo' => '2023/2/10', 'agen_id' => '64', 'jenisorder_id' => '3', 'cabang_id' => '2', 'piutang_nobukti' => 'EPT 0007/II/2023', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'tgldari' => '2023/2/1', 'tglsampai' => '2023/2/10', 'statusformat' => '151', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
        invoiceheader::create(['nobukti' => 'INV 0008/II/2023', 'tglbukti' => '2023/2/10', 'keterangan' => '', 'nominal' => '700000', 'tglterima' => '2023/2/10', 'tgljatuhtempo' => '2023/2/10', 'agen_id' => '63', 'jenisorder_id' => '0', 'cabang_id' => '2', 'piutang_nobukti' => 'EPT 0008/II/2023', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'tgldari' => '1900/1/1', 'tglsampai' => '1900/1/1', 'statusformat' => '151', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
        invoiceheader::create(['nobukti' => 'INV 0009/II/2023', 'tglbukti' => '2023/2/17', 'keterangan' => '', 'nominal' => '3000000', 'tglterima' => '2023/2/17', 'tgljatuhtempo' => '2023/2/17', 'agen_id' => '64', 'jenisorder_id' => '0', 'cabang_id' => '2', 'piutang_nobukti' => 'EPT 0009/II/2023', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'tgldari' => '1900/1/1', 'tglsampai' => '1900/1/1', 'statusformat' => '151', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
        invoiceheader::create(['nobukti' => 'INV 0010/II/2023', 'tglbukti' => '2023/2/17', 'keterangan' => 'BIAYA SEWA UNIT UNTUK PT WIRATAMA GROUP BK 9418 LO DAN BK 8007 XA SELAMA 8 HARI @766.666,66 ', 'nominal' => '12266667', 'tglterima' => '2023/2/17', 'tgljatuhtempo' => '2023/2/17', 'agen_id' => '40', 'jenisorder_id' => '0', 'cabang_id' => '2', 'piutang_nobukti' => 'EPT 0010/II/2023', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'tgldari' => '1900/1/1', 'tglsampai' => '1900/1/1', 'statusformat' => '151', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
        invoiceheader::create(['nobukti' => 'INV 0011/II/2023', 'tglbukti' => '2023/2/17', 'keterangan' => '', 'nominal' => '10953000', 'tglterima' => '2023/2/17', 'tgljatuhtempo' => '2023/2/17', 'agen_id' => '64', 'jenisorder_id' => '3', 'cabang_id' => '2', 'piutang_nobukti' => 'EPT 0011/II/2023', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'tgldari' => '2023/2/1', 'tglsampai' => '2023/2/14', 'statusformat' => '151', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
        invoiceheader::create(['nobukti' => 'INV 0012/II/2023', 'tglbukti' => '2023/2/17', 'keterangan' => '', 'nominal' => '37010000', 'tglterima' => '2023/2/17', 'tgljatuhtempo' => '2023/2/17', 'agen_id' => '64', 'jenisorder_id' => '1', 'cabang_id' => '2', 'piutang_nobukti' => 'EPT 0012/II/2023', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'tgldari' => '2023/2/1', 'tglsampai' => '2023/2/14', 'statusformat' => '151', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
        invoiceheader::create(['nobukti' => 'INV 0013/II/2023', 'tglbukti' => '2023/2/17', 'keterangan' => '', 'nominal' => '22064000', 'tglterima' => '2023/2/17', 'tgljatuhtempo' => '2023/2/17', 'agen_id' => '64', 'jenisorder_id' => '2', 'cabang_id' => '2', 'piutang_nobukti' => 'EPT 0013/II/2023', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'tgldari' => '2023/2/1', 'tglsampai' => '2023/2/14', 'statusformat' => '151', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
        invoiceheader::create(['nobukti' => 'INV 0014/II/2023', 'tglbukti' => '2023/2/17', 'keterangan' => '', 'nominal' => '8632000', 'tglterima' => '2023/2/17', 'tgljatuhtempo' => '2023/2/17', 'agen_id' => '64', 'jenisorder_id' => '2', 'cabang_id' => '2', 'piutang_nobukti' => 'EPT 0014/II/2023', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'tgldari' => '2023/2/1', 'tglsampai' => '2023/2/14', 'statusformat' => '151', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
        invoiceheader::create(['nobukti' => 'INV 0015/II/2023', 'tglbukti' => '2023/2/17', 'keterangan' => '', 'nominal' => '61664250', 'tglterima' => '2023/2/17', 'tgljatuhtempo' => '2023/2/17', 'agen_id' => '64', 'jenisorder_id' => '2', 'cabang_id' => '2', 'piutang_nobukti' => 'EPT 0015/II/2023', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'tgldari' => '2023/2/1', 'tglsampai' => '2023/2/14', 'statusformat' => '151', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
        invoiceheader::create(['nobukti' => 'INV 0016/II/2023', 'tglbukti' => '2023/2/17', 'keterangan' => '', 'nominal' => '2625000', 'tglterima' => '2023/2/17', 'tgljatuhtempo' => '2023/2/17', 'agen_id' => '64', 'jenisorder_id' => '1', 'cabang_id' => '2', 'piutang_nobukti' => 'EPT 0016/II/2023', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'tgldari' => '2023/2/1', 'tglsampai' => '2023/2/14', 'statusformat' => '151', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
        invoiceheader::create(['nobukti' => 'INV 0019/II/2023', 'tglbukti' => '2023/2/22', 'keterangan' => '', 'nominal' => '55594000', 'tglterima' => '2023/2/22', 'tgljatuhtempo' => '2023/2/22', 'agen_id' => '64', 'jenisorder_id' => '2', 'cabang_id' => '2', 'piutang_nobukti' => 'EPT 0019/II/2023', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'tgldari' => '2023/2/1', 'tglsampai' => '2023/2/18', 'statusformat' => '151', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
        invoiceheader::create(['nobukti' => 'INV 0020/II/2023', 'tglbukti' => '2023/2/22', 'keterangan' => '', 'nominal' => '15832000', 'tglterima' => '2023/2/22', 'tgljatuhtempo' => '2023/2/22', 'agen_id' => '64', 'jenisorder_id' => '2', 'cabang_id' => '2', 'piutang_nobukti' => 'EPT 0020/II/2023', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'tgldari' => '2023/2/1', 'tglsampai' => '2023/2/18', 'statusformat' => '151', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
        invoiceheader::create(['nobukti' => 'INV 0021/II/2023', 'tglbukti' => '2023/2/22', 'keterangan' => '', 'nominal' => '2862500', 'tglterima' => '2023/2/22', 'tgljatuhtempo' => '2023/2/22', 'agen_id' => '64', 'jenisorder_id' => '3', 'cabang_id' => '2', 'piutang_nobukti' => 'EPT 0021/II/2023', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'tgldari' => '2023/2/1', 'tglsampai' => '2023/2/18', 'statusformat' => '151', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
        invoiceheader::create(['nobukti' => 'INV 0022/II/2023', 'tglbukti' => '2023/2/22', 'keterangan' => '', 'nominal' => '25528500', 'tglterima' => '2023/2/22', 'tgljatuhtempo' => '2023/2/22', 'agen_id' => '64', 'jenisorder_id' => '1', 'cabang_id' => '2', 'piutang_nobukti' => 'EPT 0022/II/2023', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'tgldari' => '2023/2/1', 'tglsampai' => '2023/2/18', 'statusformat' => '151', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
        invoiceheader::create(['nobukti' => 'INV 0023/II/2023', 'tglbukti' => '2023/2/27', 'keterangan' => '', 'nominal' => '8057500', 'tglterima' => '2023/2/27', 'tgljatuhtempo' => '2023/2/27', 'agen_id' => '69', 'jenisorder_id' => '0', 'cabang_id' => '2', 'piutang_nobukti' => 'EPT 0023/II/2023', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'tgldari' => '2023/2/1', 'tglsampai' => '2023/2/24', 'statusformat' => '151', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
        invoiceheader::create(['nobukti' => 'INV 0024/II/2023', 'tglbukti' => '2023/2/28', 'keterangan' => '', 'nominal' => '4800000', 'tglterima' => '2023/2/28', 'tgljatuhtempo' => '2023/2/28', 'agen_id' => '64', 'jenisorder_id' => '2', 'cabang_id' => '2', 'piutang_nobukti' => 'EPT 0024/II/2023', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'tgldari' => '2023/2/1', 'tglsampai' => '2023/2/28', 'statusformat' => '151', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
        invoiceheader::create(['nobukti' => 'INV 0025/II/2023', 'tglbukti' => '2023/2/28', 'keterangan' => '', 'nominal' => '1915500', 'tglterima' => '2023/2/28', 'tgljatuhtempo' => '2023/2/28', 'agen_id' => '64', 'jenisorder_id' => '2', 'cabang_id' => '2', 'piutang_nobukti' => 'EPT 0025/II/2023', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'tgldari' => '2023/2/1', 'tglsampai' => '2023/2/28', 'statusformat' => '151', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
        invoiceheader::create(['nobukti' => 'INV 0026/II/2023', 'tglbukti' => '2023/2/28', 'keterangan' => '', 'nominal' => '46542000', 'tglterima' => '2023/2/28', 'tgljatuhtempo' => '2023/2/28', 'agen_id' => '64', 'jenisorder_id' => '2', 'cabang_id' => '2', 'piutang_nobukti' => 'EPT 0026/II/2023', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'tgldari' => '2023/2/1', 'tglsampai' => '2023/2/28', 'statusformat' => '151', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
        invoiceheader::create(['nobukti' => 'INV 0027/II/2023', 'tglbukti' => '2023/2/28', 'keterangan' => '', 'nominal' => '30702250', 'tglterima' => '2023/2/28', 'tgljatuhtempo' => '2023/2/28', 'agen_id' => '64', 'jenisorder_id' => '1', 'cabang_id' => '2', 'piutang_nobukti' => 'EPT 0027/II/2023', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'tgldari' => '2023/2/1', 'tglsampai' => '2023/2/28', 'statusformat' => '151', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
        invoiceheader::create(['nobukti' => 'INV 0028/II/2023', 'tglbukti' => '2023/2/28', 'keterangan' => '', 'nominal' => '4092500', 'tglterima' => '2023/2/28', 'tgljatuhtempo' => '2023/2/28', 'agen_id' => '64', 'jenisorder_id' => '3', 'cabang_id' => '2', 'piutang_nobukti' => 'EPT 0028/II/2023', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'tgldari' => '2023/2/1', 'tglsampai' => '2023/2/28', 'statusformat' => '151', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
        invoiceheader::create(['nobukti' => 'INV 0029/II/2023', 'tglbukti' => '2023/2/28', 'keterangan' => '', 'nominal' => '2625000', 'tglterima' => '2023/2/28', 'tgljatuhtempo' => '2023/2/28', 'agen_id' => '64', 'jenisorder_id' => '1', 'cabang_id' => '2', 'piutang_nobukti' => 'EPT 0029/II/2023', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'tgldari' => '2023/2/1', 'tglsampai' => '2023/2/28', 'statusformat' => '151', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
        invoiceheader::create(['nobukti' => 'INV 0030/II/2023', 'tglbukti' => '2023/2/28', 'keterangan' => '', 'nominal' => '2625000', 'tglterima' => '2023/2/28', 'tgljatuhtempo' => '2023/2/28', 'agen_id' => '64', 'jenisorder_id' => '1', 'cabang_id' => '2', 'piutang_nobukti' => 'EPT 0030/II/2023', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'tgldari' => '2023/2/1', 'tglsampai' => '2023/2/28', 'statusformat' => '151', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
        invoiceheader::create(['nobukti' => 'INV 0031/II/2023', 'tglbukti' => '2023/2/28', 'keterangan' => '', 'nominal' => '2400000', 'tglterima' => '2023/2/28', 'tgljatuhtempo' => '2023/2/28', 'agen_id' => '64', 'jenisorder_id' => '0', 'cabang_id' => '2', 'piutang_nobukti' => 'EPT 0031/II/2023', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'tgldari' => '1900/1/1', 'tglsampai' => '1900/1/1', 'statusformat' => '151', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
        invoiceheader::create(['nobukti' => 'INV 0032/II/2023', 'tglbukti' => '2023/2/28', 'keterangan' => '', 'nominal' => '4005000', 'tglterima' => '2023/2/28', 'tgljatuhtempo' => '2023/2/28', 'agen_id' => '69', 'jenisorder_id' => '0', 'cabang_id' => '2', 'piutang_nobukti' => 'EPT 0032/II/2023', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'tgldari' => '2023/2/1', 'tglsampai' => '2023/2/28', 'statusformat' => '151', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
        invoiceheader::create(['nobukti' => 'INV 0033/II/2023', 'tglbukti' => '2023/2/28', 'keterangan' => '', 'nominal' => '26557250', 'tglterima' => '2023/2/28', 'tgljatuhtempo' => '2023/2/28', 'agen_id' => '64', 'jenisorder_id' => '2', 'cabang_id' => '2', 'piutang_nobukti' => 'EPT 0033/II/2023', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'tgldari' => '2023/2/1', 'tglsampai' => '2023/2/28', 'statusformat' => '151', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
        invoiceheader::create(['nobukti' => 'INV 0034/II/2023', 'tglbukti' => '2023/2/28', 'keterangan' => '', 'nominal' => '7200000', 'tglterima' => '2023/2/28', 'tgljatuhtempo' => '2023/2/28', 'agen_id' => '64', 'jenisorder_id' => '2', 'cabang_id' => '2', 'piutang_nobukti' => 'EPT 0034/II/2023', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'tgldari' => '2023/2/1', 'tglsampai' => '2023/2/28', 'statusformat' => '151', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
        invoiceheader::create(['nobukti' => 'INV 0035/II/2023', 'tglbukti' => '2023/2/28', 'keterangan' => '', 'nominal' => '3107000', 'tglterima' => '2023/2/28', 'tgljatuhtempo' => '2023/2/28', 'agen_id' => '64', 'jenisorder_id' => '3', 'cabang_id' => '2', 'piutang_nobukti' => 'EPT 0035/II/2023', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'tgldari' => '2023/2/1', 'tglsampai' => '2023/2/28', 'statusformat' => '151', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
        invoiceheader::create(['nobukti' => 'INV 0036/II/2023', 'tglbukti' => '2023/2/28', 'keterangan' => '', 'nominal' => '13231750', 'tglterima' => '2023/2/28', 'tgljatuhtempo' => '2023/2/28', 'agen_id' => '64', 'jenisorder_id' => '1', 'cabang_id' => '2', 'piutang_nobukti' => 'EPT 0036/II/2023', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'tgldari' => '2023/2/1', 'tglsampai' => '2023/2/28', 'statusformat' => '151', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
        invoiceheader::create(['nobukti' => 'INV 0037/II/2023', 'tglbukti' => '2023/2/28', 'keterangan' => '', 'nominal' => '1431250', 'tglterima' => '2023/2/28', 'tgljatuhtempo' => '2023/2/28', 'agen_id' => '69', 'jenisorder_id' => '0', 'cabang_id' => '2', 'piutang_nobukti' => 'EPT 0037/II/2023', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'tgldari' => '2023/2/1', 'tglsampai' => '2023/2/28', 'statusformat' => '151', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
        invoiceheader::create(['nobukti' => 'INV 0038/II/2023', 'tglbukti' => '2023/2/28', 'keterangan' => '', 'nominal' => '36898500', 'tglterima' => '2023/2/28', 'tgljatuhtempo' => '2023/2/28', 'agen_id' => '64', 'jenisorder_id' => '2', 'cabang_id' => '2', 'piutang_nobukti' => 'EPT 0038/II/2023', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'tgldari' => '2023/2/1', 'tglsampai' => '2023/2/28', 'statusformat' => '151', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
        invoiceheader::create(['nobukti' => 'INV 0039/II/2023', 'tglbukti' => '2023/2/28', 'keterangan' => '', 'nominal' => '13883500', 'tglterima' => '2023/2/28', 'tgljatuhtempo' => '2023/2/28', 'agen_id' => '64', 'jenisorder_id' => '1', 'cabang_id' => '2', 'piutang_nobukti' => 'EPT 0039/II/2023', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'tgldari' => '2023/2/1', 'tglsampai' => '2023/2/28', 'statusformat' => '151', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
        invoiceheader::create(['nobukti' => 'INV 0040/II/2023', 'tglbukti' => '2023/2/28', 'keterangan' => '', 'nominal' => '22931500', 'tglterima' => '2023/2/28', 'tgljatuhtempo' => '2023/2/28', 'agen_id' => '64', 'jenisorder_id' => '3', 'cabang_id' => '2', 'piutang_nobukti' => 'EPT 0040/II/2023', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'tgldari' => '2023/2/1', 'tglsampai' => '2023/2/28', 'statusformat' => '151', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
        invoiceheader::create(['nobukti' => 'INV 0041/II/2023', 'tglbukti' => '2023/2/28', 'keterangan' => '', 'nominal' => '1800000', 'tglterima' => '2023/2/28', 'tgljatuhtempo' => '2023/2/28', 'agen_id' => '34', 'jenisorder_id' => '0', 'cabang_id' => '2', 'piutang_nobukti' => 'EPT 0041/II/2023', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'tgldari' => '1900/1/1', 'tglsampai' => '1900/1/1', 'statusformat' => '151', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
    }
}
