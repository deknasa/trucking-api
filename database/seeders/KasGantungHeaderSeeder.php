<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\KasGantungHeader;
use Illuminate\Support\Facades\DB;

class KasGantungHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete KasGantungHeader");
        DB::statement("DBCC CHECKIDENT ('KasGantungHeader', RESEED, 1);");

        kasgantungheader::create(['nobukti' => 'KGT 0002/VI/2012', 'tglbukti' => '2012/6/30', 'keterangan' => 'UANG JAMINAN PINJAM TABUNG GAS OKSIGEN SAMA ATB TGL 30/6-10 ( TABUNG DI BALIKKAN UANG KEMBALI)', 'penerima' => '', 'penerima_id' => '0', 'bank_id' => '1', 'pengeluaran_nobukti' => '', 'coakaskeluar' => '', 'postingdari' => '', 'tglkaskeluar' => '2012/6/30', 'statusformat' => '52', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
        kasgantungheader::create(['nobukti' => 'KGT 0019/XI/2014', 'tglbukti' => '2014/11/8', 'keterangan' => 'B. JAMINAN 1 BH TABUNG OKSIGEN DARI ATB  ', 'penerima' => '', 'penerima_id' => '0', 'bank_id' => '1', 'pengeluaran_nobukti' => '', 'coakaskeluar' => '', 'postingdari' => '', 'tglkaskeluar' => '2014/11/8', 'statusformat' => '52', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
        kasgantungheader::create(['nobukti' => 'KGT 0038/IV/2023', 'tglbukti' => '2023/4/26', 'keterangan' => 'UANG JALAN SUPIR TGL 26-APR-2023', 'penerima' => '', 'penerima_id' => '0', 'bank_id' => '1', 'pengeluaran_nobukti' => '', 'coakaskeluar' => '', 'postingdari' => '', 'tglkaskeluar' => '2023/4/26', 'statusformat' => '52', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
        kasgantungheader::create(['nobukti' => 'KGT 0041/IV/2023', 'tglbukti' => '2023/4/27', 'keterangan' => 'UANG JALAN SUPIR TGL 27-APR-2023', 'penerima' => '', 'penerima_id' => '0', 'bank_id' => '1', 'pengeluaran_nobukti' => '', 'coakaskeluar' => '', 'postingdari' => '', 'tglkaskeluar' => '2023/4/27', 'statusformat' => '52', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
        kasgantungheader::create(['nobukti' => 'KGT 0043/IV/2023', 'tglbukti' => '2023/4/28', 'keterangan' => 'UANG JALAN SUPIR TGL 28-APR-2023', 'penerima' => '', 'penerima_id' => '0', 'bank_id' => '1', 'pengeluaran_nobukti' => '', 'coakaskeluar' => '', 'postingdari' => '', 'tglkaskeluar' => '2023/4/28', 'statusformat' => '52', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
        kasgantungheader::create(['nobukti' => 'KGT 0046/IV/2023', 'tglbukti' => '2023/4/28', 'keterangan' => 'KAS GANTUNG UNTUK UANG JALAN SUPIR TGL. 02 MEI 2023', 'penerima' => '', 'penerima_id' => '0', 'bank_id' => '1', 'pengeluaran_nobukti' => '', 'coakaskeluar' => '', 'postingdari' => '', 'tglkaskeluar' => '2023/4/28', 'statusformat' => '52', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
        kasgantungheader::create(['nobukti' => 'KGT 0047/IV/2023', 'tglbukti' => '2023/4/29', 'keterangan' => 'UANG JALAN SUPIR TGL 29-APR-2023', 'penerima' => '', 'penerima_id' => '0', 'bank_id' => '1', 'pengeluaran_nobukti' => '', 'coakaskeluar' => '', 'postingdari' => '', 'tglkaskeluar' => '2023/4/29', 'statusformat' => '52', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
        kasgantungheader::create(['nobukti' => 'KGT 0048/IV/2023', 'tglbukti' => '2023/4/30', 'keterangan' => 'UANG JALAN SUPIR TGL 30-APR-2023', 'penerima' => '', 'penerima_id' => '0', 'bank_id' => '1', 'pengeluaran_nobukti' => '', 'coakaskeluar' => '', 'postingdari' => '', 'tglkaskeluar' => '2023/4/30', 'statusformat' => '52', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
    }
}
