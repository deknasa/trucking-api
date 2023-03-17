<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JurnalUmumHeader;
use Illuminate\Support\Facades\DB;

class JurnalUmumHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete jurnalumumheader");
        DB::statement("DBCC CHECKIDENT ('jurnalumumheader', RESEED, 1);");


        jurnalumumheader::create(['nobukti' => 'BMT-M BCA3 0001/II/2023', 'tglbukti' => '2023/2/1', 'keterangan' => '', 'postingdari' => 'ENTRY PENERIMAAN KAS/BANK', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'statusformat' => '0', 'modifiedby' => 'ADMIN',]);
        jurnalumumheader::create(['nobukti' => 'KBT 0001/II/2023', 'tglbukti' => '2023/2/1', 'keterangan' => '', 'postingdari' => 'ENTRY PENGELUARAN TRUCKING', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'statusformat' => '0', 'modifiedby' => 'ADMIN',]);
        jurnalumumheader::create(['nobukti' => 'KBT 0002/II/2023', 'tglbukti' => '2023/2/1', 'keterangan' => '', 'postingdari' => 'ENTRY PENGELUARAN TRUCKING', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'statusformat' => '0', 'modifiedby' => 'ADMIN',]);
        jurnalumumheader::create(['nobukti' => 'KBT 0003/II/2023', 'tglbukti' => '2023/2/1', 'keterangan' => '', 'postingdari' => 'ENTRY PENGELUARAN TRUCKING', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'statusformat' => '0', 'modifiedby' => 'ADMIN',]);
        jurnalumumheader::create(['nobukti' => 'KBT 0004/II/2023', 'tglbukti' => '2023/2/1', 'keterangan' => '', 'postingdari' => 'ENTRY PENGELUARAN TRUCKING', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'statusformat' => '0', 'modifiedby' => 'ADMIN',]);
        jurnalumumheader::create(['nobukti' => 'BMT-M BCA3 0002/II/2023', 'tglbukti' => '2023/2/1', 'keterangan' => '', 'postingdari' => 'ENTRY PELUNASAN PIUTANG', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'statusformat' => '0', 'modifiedby' => 'ADMIN',]);
        jurnalumumheader::create(['nobukti' => 'BMT-M BCA3 0003/II/2023', 'tglbukti' => '2023/2/1', 'keterangan' => '', 'postingdari' => 'ENTRY PELUNASAN PIUTANG', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'statusformat' => '0', 'modifiedby' => 'ADMIN',]);
        jurnalumumheader::create(['nobukti' => 'BMT-M BCA3 0004/II/2023', 'tglbukti' => '2023/2/7', 'keterangan' => '', 'postingdari' => 'ENTRY PENERIMAAN KAS/BANK', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'statusformat' => '0', 'modifiedby' => 'ADMIN',]);
        jurnalumumheader::create(['nobukti' => 'BMT-M BCA3 0005/II/2023', 'tglbukti' => '2023/2/7', 'keterangan' => '', 'postingdari' => 'ENTRY PENERIMAAN KAS/BANK', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'statusformat' => '0', 'modifiedby' => 'ADMIN',]);
        jurnalumumheader::create(['nobukti' => 'BMT-M BCA3 0006/II/2023', 'tglbukti' => '2023/2/7', 'keterangan' => '', 'postingdari' => 'ENTRY PENERIMAAN KAS/BANK', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'statusformat' => '0', 'modifiedby' => 'ADMIN',]);
        jurnalumumheader::create(['nobukti' => 'BMT-M BCA3 0007/II/2023', 'tglbukti' => '2023/2/7', 'keterangan' => '', 'postingdari' => 'ENTRY PENERIMAAN KAS/BANK', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'statusformat' => '0', 'modifiedby' => 'ADMIN',]);
        jurnalumumheader::create(['nobukti' => 'BMT-M BCA3 0008/II/2023', 'tglbukti' => '2023/2/8', 'keterangan' => '', 'postingdari' => 'ENTRY PENERIMAAN KAS/BANK', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'statusformat' => '0', 'modifiedby' => 'ADMIN',]);
        jurnalumumheader::create(['nobukti' => 'BMT-M BCA3 0009/II/2023', 'tglbukti' => '2023/2/8', 'keterangan' => '', 'postingdari' => 'ENTRY PENERIMAAN KAS/BANK', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'statusformat' => '0', 'modifiedby' => 'ADMIN',]);
        jurnalumumheader::create(['nobukti' => 'BMT-M BCA3 0010/II/2023', 'tglbukti' => '2023/2/9', 'keterangan' => '', 'postingdari' => 'ENTRY PELUNASAN PIUTANG', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'statusformat' => '0', 'modifiedby' => 'ADMIN',]);
        jurnalumumheader::create(['nobukti' => 'BMT-M BCA3 0011/II/2023', 'tglbukti' => '2023/2/10', 'keterangan' => '', 'postingdari' => 'ENTRY PENERIMAAN KAS/BANK', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'statusformat' => '0', 'modifiedby' => 'ADMIN',]);
        jurnalumumheader::create(['nobukti' => 'BMT-M BCA3 0012/II/2023', 'tglbukti' => '2023/2/15', 'keterangan' => '', 'postingdari' => 'ENTRY PENERIMAAN KAS/BANK', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'statusformat' => '0', 'modifiedby' => 'ADMIN',]);
        jurnalumumheader::create(['nobukti' => 'BMT-M BCA3 0013/II/2023', 'tglbukti' => '2023/2/16', 'keterangan' => '', 'postingdari' => 'ENTRY PELUNASAN PIUTANG', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'statusformat' => '0', 'modifiedby' => 'ADMIN',]);
        jurnalumumheader::create(['nobukti' => 'BMT-M BCA3 0014/II/2023', 'tglbukti' => '2023/2/17', 'keterangan' => '', 'postingdari' => 'ENTRY PENERIMAAN KAS/BANK', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'statusformat' => '0', 'modifiedby' => 'ADMIN',]);
        jurnalumumheader::create(['nobukti' => 'BMT-M BCA3 0015/II/2023', 'tglbukti' => '2023/2/22', 'keterangan' => '', 'postingdari' => 'ENTRY PENERIMAAN KAS/BANK', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'statusformat' => '0', 'modifiedby' => 'ADMIN',]);
        jurnalumumheader::create(['nobukti' => 'BMT-M BCA3 0016/II/2023', 'tglbukti' => '2023/2/23', 'keterangan' => '', 'postingdari' => 'ENTRY PELUNASAN PIUTANG', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'statusformat' => '0', 'modifiedby' => 'ADMIN',]);
        jurnalumumheader::create(['nobukti' => 'BMT-M BCA3 0017/II/2023', 'tglbukti' => '2023/2/24', 'keterangan' => '', 'postingdari' => 'ENTRY PENERIMAAN KAS/BANK', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'statusformat' => '0', 'modifiedby' => 'ADMIN',]);
    }
}
