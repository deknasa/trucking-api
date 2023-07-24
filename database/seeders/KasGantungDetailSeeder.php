<?php

namespace Database\Seeders;

use App\Models\KasGantungDetail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KasGantungDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete KasGantungDetail");
        DB::statement("DBCC CHECKIDENT ('KasGantungDetail', RESEED, 1);");

        kasgantungdetail::create([ 'kasgantung_id' => '1', 'nobukti' => 'KGT 0002/VI/2012', 'nominal' => '1000000.00', 'coa' => '', 'keterangan' => 'UANG JAMINAN PINJAM TABUNG GAS OKSIGEN SAMA ATB TGL 30/6-10 ( TABUNG DI BALIKKAN UANG KEMBALI)', 'modifiedby' => 'ADMIN',]);
        kasgantungdetail::create([ 'kasgantung_id' => '2', 'nobukti' => 'KGT 0019/XI/2014', 'nominal' => '1000000.00', 'coa' => '', 'keterangan' => 'B. JAMINAN 1 BH TABUNG OKSIGEN DARI ATB  ', 'modifiedby' => 'ADMIN',]);
        kasgantungdetail::create([ 'kasgantung_id' => '3', 'nobukti' => 'KGT 0038/IV/2023', 'nominal' => '1100000.00', 'coa' => '', 'keterangan' => 'UANG JALAN SUPIR TGL 26-APR-2023', 'modifiedby' => 'ADMIN',]);
        kasgantungdetail::create([ 'kasgantung_id' => '4', 'nobukti' => 'KGT 0041/IV/2023', 'nominal' => '600000.00', 'coa' => '', 'keterangan' => 'UANG JALAN SUPIR TGL 27-APR-2023', 'modifiedby' => 'ADMIN',]);
        kasgantungdetail::create([ 'kasgantung_id' => '5', 'nobukti' => 'KGT 0043/IV/2023', 'nominal' => '1300000.00', 'coa' => '', 'keterangan' => 'UANG JALAN SUPIR TGL 28-APR-2023', 'modifiedby' => 'ADMIN',]);
        kasgantungdetail::create([ 'kasgantung_id' => '6', 'nobukti' => 'KGT 0046/IV/2023', 'nominal' => '3800000.00', 'coa' => '', 'keterangan' => 'KAS GANTUNG UNTUK UANG JALAN SUPIR TGL. 02 MEI 2023', 'modifiedby' => 'ADMIN',]);
        kasgantungdetail::create([ 'kasgantung_id' => '7', 'nobukti' => 'KGT 0047/IV/2023', 'nominal' => '1200000.00', 'coa' => '', 'keterangan' => 'UANG JALAN SUPIR TGL 29-APR-2023', 'modifiedby' => 'ADMIN',]);
        kasgantungdetail::create([ 'kasgantung_id' => '8', 'nobukti' => 'KGT 0048/IV/2023', 'nominal' => '200000.00', 'coa' => '', 'keterangan' => 'UANG JALAN SUPIR TGL 30-APR-2023', 'modifiedby' => 'ADMIN',]);

    }
}
