<?php

namespace Database\Seeders;

use App\Models\AbsensiSupirHeader;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AbsensiSupirHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete AbsensiSupirHeader");
        DB::statement("DBCC CHECKIDENT ('AbsensiSupirHeader', RESEED, 1);");

        absensisupirheader::create(['nobukti' => 'ABS 0001/II/2023', 'tglbukti' => '2023/2/1', 'keterangan' => '', 'kasgantung_nobukti' => 'KGT 0001/II/2023', 'nominal' => '1600000', 'statusformat' => '5', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'statusapprovaleditabsensi' => '248', 'userapprovaleditabsensi' => 'ADMIN', 'tglapprovaleditabsensi' => '2023/3/20', 'modifiedby' => 'ADMIN',]);
        absensisupirheader::create(['nobukti' => 'ABS 0002/II/2023', 'tglbukti' => '2023/2/2', 'keterangan' => '', 'kasgantung_nobukti' => 'KGT 0002/II/2023', 'nominal' => '1725000', 'statusformat' => '5', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'statusapprovaleditabsensi' => '249', 'userapprovaleditabsensi' => '', 'tglapprovaleditabsensi' => '1900/1/1', 'modifiedby' => 'ADMIN',]);
        absensisupirheader::create(['nobukti' => 'ABS 0001/I/2023', 'tglbukti' => '2023/1/27', 'keterangan' => 'UANG JALAN SUPIR TANGGAL 27 JANUARI 2023', 'kasgantung_nobukti' => 'KGT 0053/I/2023', 'nominal' => '1500000', 'statusformat' => '5', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'statusapprovaleditabsensi' => '249', 'userapprovaleditabsensi' => '', 'tglapprovaleditabsensi' => '1900/1/1', 'modifiedby' => 'ADMIN',]);
        absensisupirheader::create(['nobukti' => 'ABS 0002/I/2023', 'tglbukti' => '2023/1/28', 'keterangan' => 'UANG JALAN SUPIR TANGGAL 28 JANUARI 2023', 'kasgantung_nobukti' => 'KGT 0057/I/2023', 'nominal' => '1475000', 'statusformat' => '5', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'statusapprovaleditabsensi' => '249', 'userapprovaleditabsensi' => '', 'tglapprovaleditabsensi' => '1900/1/1', 'modifiedby' => 'ADMIN',]);
        absensisupirheader::create(['nobukti' => 'ABS 0003/I/2023', 'tglbukti' => '2023/1/29', 'keterangan' => 'ABSENSI UNTUK SUPIR YANG BERANGKAT ANTAR BARANG PADA HARI MINGGU', 'kasgantung_nobukti' => 'KGT 0072/I/2023', 'nominal' => '0', 'statusformat' => '5', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'statusapprovaleditabsensi' => '249', 'userapprovaleditabsensi' => '', 'tglapprovaleditabsensi' => '1900/1/1', 'modifiedby' => 'ADMIN',]);
        absensisupirheader::create(['nobukti' => 'ABS 0004/I/2023', 'tglbukti' => '2023/1/30', 'keterangan' => 'UANG JALAN SUPIR TANGGAL 30 JANUARI 2023', 'kasgantung_nobukti' => 'KGT 0064/I/2023', 'nominal' => '1625000', 'statusformat' => '5', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'statusapprovaleditabsensi' => '249', 'userapprovaleditabsensi' => '', 'tglapprovaleditabsensi' => '1900/1/1', 'modifiedby' => 'ADMIN',]);
    }
}
