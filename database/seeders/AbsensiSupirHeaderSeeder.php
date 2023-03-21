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
    }
}
