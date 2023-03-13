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
    }
}
