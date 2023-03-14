<?php

namespace Database\Seeders;

use App\Models\JurnalUmumDetail;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JurnalUmumDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete jurnalumumdetail");
        DB::statement("DBCC CHECKIDENT ('jurnalumumdetail', RESEED, 1);");

        jurnalumumdetail::create(['jurnalumum_id' => '1', 'nobukti' => 'BMT-M BCA3 0001/II/2023', 'tglbukti' => '2023/2/1', 'coa' => '01.02.02.05', 'nominal' => '90000000', 'keterangan' => 'DITERIMA DARI KANTOR PUSAT DANA MINGGUAN TRUCKING', 'baris' => '0', 'modifiedby' => 'ADMIN',]);
        jurnalumumdetail::create(['jurnalumum_id' => '1', 'nobukti' => 'BMT-M BCA3 0001/II/2023', 'tglbukti' => '2023/2/1', 'coa' => '05.03.01.01', 'nominal' => '-90000000', 'keterangan' => 'DITERIMA DARI KANTOR PUSAT DANA MINGGUAN TRUCKING', 'baris' => '0', 'modifiedby' => 'ADMIN',]);
        jurnalumumdetail::create(['jurnalumum_id' => '1', 'nobukti' => 'BMT-M BCA3 0001/II/2023', 'tglbukti' => '2023/2/1', 'coa' => '01.02.02.05', 'nominal' => '100000', 'keterangan' => 'DITERIMA DARI KANTOR PUSAT ATAS PENGEMBALIAN BIAYA TOP UP SALDO TOL CARD MOBIL BP. ACHIANG TGL. 17 JANUARI 2023', 'baris' => '1', 'modifiedby' => 'ADMIN',]);
        jurnalumumdetail::create(['jurnalumum_id' => '1', 'nobukti' => 'BMT-M BCA3 0001/II/2023', 'tglbukti' => '2023/2/1', 'coa' => '03.02.02.12', 'nominal' => '-100000', 'keterangan' => 'DITERIMA DARI KANTOR PUSAT ATAS PENGEMBALIAN BIAYA TOP UP SALDO TOL CARD MOBIL BP. ACHIANG TGL. 17 JANUARI 2023', 'baris' => '1', 'modifiedby' => 'ADMIN',]);
        jurnalumumdetail::create(['jurnalumum_id' => '1', 'nobukti' => 'BMT-M BCA3 0001/II/2023', 'tglbukti' => '2023/2/1', 'coa' => '01.02.02.05', 'nominal' => '100000', 'keterangan' => 'DITERIMA DARI KANTOR PUSAT ATAS PENGEMBALIAN BIAYA TOP UP SALDO TOL CARD MOBIL BP. ACHIANG TGL. 19 JANUARI 2023', 'baris' => '2', 'modifiedby' => 'ADMIN',]);
        jurnalumumdetail::create(['jurnalumum_id' => '1', 'nobukti' => 'BMT-M BCA3 0001/II/2023', 'tglbukti' => '2023/2/1', 'coa' => '03.02.02.12', 'nominal' => '-100000', 'keterangan' => 'DITERIMA DARI KANTOR PUSAT ATAS PENGEMBALIAN BIAYA TOP UP SALDO TOL CARD MOBIL BP. ACHIANG TGL. 19 JANUARI 2023', 'baris' => '2', 'modifiedby' => 'ADMIN',]);
        jurnalumumdetail::create(['jurnalumum_id' => '1', 'nobukti' => 'BMT-M BCA3 0001/II/2023', 'tglbukti' => '2023/2/1', 'coa' => '01.02.02.05', 'nominal' => '100000', 'keterangan' => 'DITERIMA DARI KANTOR PUSAT ATAS PENGEMBALIAN BIAYA TOP UP SALDO TOL CARD MOBIL BP. ACHIANG TGL. 25 JANUARI 2023', 'baris' => '3', 'modifiedby' => 'ADMIN',]);
        jurnalumumdetail::create(['jurnalumum_id' => '1', 'nobukti' => 'BMT-M BCA3 0001/II/2023', 'tglbukti' => '2023/2/1', 'coa' => '03.02.02.12', 'nominal' => '-100000', 'keterangan' => 'DITERIMA DARI KANTOR PUSAT ATAS PENGEMBALIAN BIAYA TOP UP SALDO TOL CARD MOBIL BP. ACHIANG TGL. 25 JANUARI 2023', 'baris' => '3', 'modifiedby' => 'ADMIN',]);
        jurnalumumdetail::create(['jurnalumum_id' => '1', 'nobukti' => 'BMT-M BCA3 0001/II/2023', 'tglbukti' => '2023/2/1', 'coa' => '01.02.02.05', 'nominal' => '1290500', 'keterangan' => 'DITERIMA DARI KANTOR PUSAT ATAS PENGEMBALIAN BIAYA BBM MOBIL BP. ACHIANG TGL. 27 JANUARI 2022', 'baris' => '4', 'modifiedby' => 'ADMIN',]);
        jurnalumumdetail::create(['jurnalumum_id' => '1', 'nobukti' => 'BMT-M BCA3 0001/II/2023', 'tglbukti' => '2023/2/1', 'coa' => '03.02.02.12', 'nominal' => '-1290500', 'keterangan' => 'DITERIMA DARI KANTOR PUSAT ATAS PENGEMBALIAN BIAYA BBM MOBIL BP. ACHIANG TGL. 27 JANUARI 2022', 'baris' => '4', 'modifiedby' => 'ADMIN',]);
        jurnalumumdetail::create(['jurnalumum_id' => '2', 'nobukti' => 'KBT 0001/II/2023', 'tglbukti' => '2023/2/1', 'coa' => '01.05.02.02', 'nominal' => '19645', 'keterangan' => 'GAJI MINUS SUPIR CHANDRA BK 8743 BU TGL. 01 FEBRUARI 2023', 'baris' => '0', 'modifiedby' => 'ADMIN',]);
        jurnalumumdetail::create(['jurnalumum_id' => '2', 'nobukti' => 'KBT 0001/II/2023', 'tglbukti' => '2023/2/1', 'coa' => '01.01.01.02', 'nominal' => '-19645', 'keterangan' => 'GAJI MINUS SUPIR CHANDRA BK 8743 BU TGL. 01 FEBRUARI 2023', 'baris' => '0', 'modifiedby' => 'ADMIN',]);
        jurnalumumdetail::create(['jurnalumum_id' => '3', 'nobukti' => 'KBT 0002/II/2023', 'tglbukti' => '2023/2/1', 'coa' => '01.05.02.02', 'nominal' => '100780', 'keterangan' => 'GAJI MINUS SUPIR ERIKSON BK 8264 FB TGL. 01 FEBRUARI 2023', 'baris' => '0', 'modifiedby' => 'ADMIN',]);
        jurnalumumdetail::create(['jurnalumum_id' => '3', 'nobukti' => 'KBT 0002/II/2023', 'tglbukti' => '2023/2/1', 'coa' => '01.01.01.02', 'nominal' => '-100780', 'keterangan' => 'GAJI MINUS SUPIR ERIKSON BK 8264 FB TGL. 01 FEBRUARI 2023', 'baris' => '0', 'modifiedby' => 'ADMIN',]);
        jurnalumumdetail::create(['jurnalumum_id' => '4', 'nobukti' => 'KBT 0003/II/2023', 'tglbukti' => '2023/2/1', 'coa' => '01.05.02.02', 'nominal' => '33408', 'keterangan' => 'GAJI MINUS SUPIR SAHBUDIN  BK 8178 EW TGL. 01 FEBRUARI 2023', 'baris' => '0', 'modifiedby' => 'ADMIN',]);
        jurnalumumdetail::create(['jurnalumum_id' => '4', 'nobukti' => 'KBT 0003/II/2023', 'tglbukti' => '2023/2/1', 'coa' => '01.01.01.02', 'nominal' => '-33408', 'keterangan' => 'GAJI MINUS SUPIR SAHBUDIN  BK 8178 EW TGL. 01 FEBRUARI 2023', 'baris' => '0', 'modifiedby' => 'ADMIN',]);
        jurnalumumdetail::create(['jurnalumum_id' => '5', 'nobukti' => 'KBT 0004/II/2023', 'tglbukti' => '2023/2/1', 'coa' => '01.05.02.02', 'nominal' => '215736', 'keterangan' => 'GAJI MINUS SUPIR SULAIMAN B 9668 QZ TGL. 01 FEBRUARI 2023', 'baris' => '0', 'modifiedby' => 'ADMIN',]);
        jurnalumumdetail::create(['jurnalumum_id' => '5', 'nobukti' => 'KBT 0004/II/2023', 'tglbukti' => '2023/2/1', 'coa' => '01.01.01.02', 'nominal' => '-215736', 'keterangan' => 'GAJI MINUS SUPIR SULAIMAN B 9668 QZ TGL. 01 FEBRUARI 2023', 'baris' => '0', 'modifiedby' => 'ADMIN',]);
    }
}
