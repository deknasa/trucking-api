<?php

namespace Database\Seeders;

use App\Models\AbsensiSupirDetail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AbsensiSupirDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete AbsensiSupirDetail");
        DB::statement("DBCC CHECKIDENT ('AbsensiSupirDetail', RESEED, 1);");

        absensisupirdetail::create(['absensi_id' => '1', 'nobukti' => 'ABS 0001/II/2023', 'trado_id' => '1', 'supir_id' => '0', 'keterangan' => 'STANDARISASI', 'uangjalan' => '0', 'absen_id' => '6', 'jam' => '00:00:00.0000000', 'modifiedby' => 'ADMIN',]);
        absensisupirdetail::create(['absensi_id' => '1', 'nobukti' => 'ABS 0001/II/2023', 'trado_id' => '6', 'supir_id' => '146', 'keterangan' => '', 'uangjalan' => '100000', 'absen_id' => '0', 'jam' => '00:00:00.0000000', 'modifiedby' => 'ADMIN',]);
        absensisupirdetail::create(['absensi_id' => '1', 'nobukti' => 'ABS 0001/II/2023', 'trado_id' => '7', 'supir_id' => '94', 'keterangan' => '', 'uangjalan' => '100000', 'absen_id' => '0', 'jam' => '00:00:00.0000000', 'modifiedby' => 'ADMIN',]);
        absensisupirdetail::create(['absensi_id' => '1', 'nobukti' => 'ABS 0001/II/2023', 'trado_id' => '8', 'supir_id' => '298', 'keterangan' => '', 'uangjalan' => '100000', 'absen_id' => '0', 'jam' => '00:00:00.0000000', 'modifiedby' => 'ADMIN',]);
        absensisupirdetail::create(['absensi_id' => '1', 'nobukti' => 'ABS 0001/II/2023', 'trado_id' => '11', 'supir_id' => '305', 'keterangan' => '', 'uangjalan' => '100000', 'absen_id' => '0', 'jam' => '00:00:00.0000000', 'modifiedby' => 'ADMIN',]);
        absensisupirdetail::create(['absensi_id' => '1', 'nobukti' => 'ABS 0001/II/2023', 'trado_id' => '12', 'supir_id' => '307', 'keterangan' => '', 'uangjalan' => '100000', 'absen_id' => '0', 'jam' => '00:00:00.0000000', 'modifiedby' => 'ADMIN',]);
        absensisupirdetail::create(['absensi_id' => '1', 'nobukti' => 'ABS 0001/II/2023', 'trado_id' => '14', 'supir_id' => '0', 'keterangan' => 'TIDAK ADA SUPIR', 'uangjalan' => '0', 'absen_id' => '2', 'jam' => '00:00:00.0000000', 'modifiedby' => 'ADMIN',]);
        absensisupirdetail::create(['absensi_id' => '1', 'nobukti' => 'ABS 0001/II/2023', 'trado_id' => '15', 'supir_id' => '171', 'keterangan' => '', 'uangjalan' => '100000', 'absen_id' => '0', 'jam' => '00:00:00.0000000', 'modifiedby' => 'ADMIN',]);
        absensisupirdetail::create(['absensi_id' => '1', 'nobukti' => 'ABS 0001/II/2023', 'trado_id' => '16', 'supir_id' => '72', 'keterangan' => '', 'uangjalan' => '100000', 'absen_id' => '0', 'jam' => '00:00:00.0000000', 'modifiedby' => 'ADMIN',]);
        absensisupirdetail::create(['absensi_id' => '1', 'nobukti' => 'ABS 0001/II/2023', 'trado_id' => '17', 'supir_id' => '0', 'keterangan' => 'TIDAK ADA SUPIR', 'uangjalan' => '0', 'absen_id' => '2', 'jam' => '00:00:00.0000000', 'modifiedby' => 'ADMIN',]);
        absensisupirdetail::create(['absensi_id' => '1', 'nobukti' => 'ABS 0001/II/2023', 'trado_id' => '18', 'supir_id' => '0', 'keterangan' => 'TIDAK ADA SUPIR', 'uangjalan' => '0', 'absen_id' => '2', 'jam' => '00:00:00.0000000', 'modifiedby' => 'ADMIN',]);
        absensisupirdetail::create(['absensi_id' => '1', 'nobukti' => 'ABS 0001/II/2023', 'trado_id' => '19', 'supir_id' => '76', 'keterangan' => '', 'uangjalan' => '100000', 'absen_id' => '0', 'jam' => '00:00:00.0000000', 'modifiedby' => 'ADMIN',]);
        absensisupirdetail::create(['absensi_id' => '1', 'nobukti' => 'ABS 0001/II/2023', 'trado_id' => '22', 'supir_id' => '172', 'keterangan' => '', 'uangjalan' => '100000', 'absen_id' => '0', 'jam' => '00:00:00.0000000', 'modifiedby' => 'ADMIN',]);
        absensisupirdetail::create(['absensi_id' => '1', 'nobukti' => 'ABS 0001/II/2023', 'trado_id' => '24', 'supir_id' => '175', 'keterangan' => '', 'uangjalan' => '100000', 'absen_id' => '0', 'jam' => '00:00:00.0000000', 'modifiedby' => 'ADMIN',]);
        absensisupirdetail::create(['absensi_id' => '1', 'nobukti' => 'ABS 0001/II/2023', 'trado_id' => '25', 'supir_id' => '311', 'keterangan' => '', 'uangjalan' => '100000', 'absen_id' => '0', 'jam' => '00:00:00.0000000', 'modifiedby' => 'ADMIN',]);
        absensisupirdetail::create(['absensi_id' => '1', 'nobukti' => 'ABS 0001/II/2023', 'trado_id' => '27', 'supir_id' => '60', 'keterangan' => '', 'uangjalan' => '100000', 'absen_id' => '0', 'jam' => '00:00:00.0000000', 'modifiedby' => 'ADMIN',]);
        absensisupirdetail::create(['absensi_id' => '1', 'nobukti' => 'ABS 0001/II/2023', 'trado_id' => '28', 'supir_id' => '0', 'keterangan' => 'TIDAK ADA SUPIR', 'uangjalan' => '0', 'absen_id' => '2', 'jam' => '00:00:00.0000000', 'modifiedby' => 'ADMIN',]);
        absensisupirdetail::create(['absensi_id' => '1', 'nobukti' => 'ABS 0001/II/2023', 'trado_id' => '30', 'supir_id' => '10', 'keterangan' => '', 'uangjalan' => '100000', 'absen_id' => '0', 'jam' => '00:00:00.0000000', 'modifiedby' => 'ADMIN',]);
        absensisupirdetail::create(['absensi_id' => '1', 'nobukti' => 'ABS 0001/II/2023', 'trado_id' => '34', 'supir_id' => '0', 'keterangan' => 'TIDAK ADA SUPIR', 'uangjalan' => '0', 'absen_id' => '2', 'jam' => '00:00:00.0000000', 'modifiedby' => 'ADMIN',]);
        absensisupirdetail::create(['absensi_id' => '1', 'nobukti' => 'ABS 0001/II/2023', 'trado_id' => '35', 'supir_id' => '0', 'keterangan' => 'TIDAK ADA SUPIR', 'uangjalan' => '0', 'absen_id' => '2', 'jam' => '00:00:00.0000000', 'modifiedby' => 'ADMIN',]);
        absensisupirdetail::create(['absensi_id' => '1', 'nobukti' => 'ABS 0001/II/2023', 'trado_id' => '38', 'supir_id' => '73', 'keterangan' => '', 'uangjalan' => '100000', 'absen_id' => '0', 'jam' => '00:00:00.0000000', 'modifiedby' => 'ADMIN',]);
        absensisupirdetail::create(['absensi_id' => '1', 'nobukti' => 'ABS 0001/II/2023', 'trado_id' => '39', 'supir_id' => '267', 'keterangan' => '', 'uangjalan' => '100000', 'absen_id' => '0', 'jam' => '00:00:00.0000000', 'modifiedby' => 'ADMIN',]);
        absensisupirdetail::create(['absensi_id' => '1', 'nobukti' => 'ABS 0001/II/2023', 'trado_id' => '40', 'supir_id' => '7', 'keterangan' => '', 'uangjalan' => '100000', 'absen_id' => '0', 'jam' => '00:00:00.0000000', 'modifiedby' => 'ADMIN',]);
        absensisupirdetail::create(['absensi_id' => '1', 'nobukti' => 'ABS 0001/II/2023', 'trado_id' => '42', 'supir_id' => '0', 'keterangan' => '', 'uangjalan' => '0', 'absen_id' => '0', 'jam' => '00:00:00.0000000', 'modifiedby' => 'ADMIN',]);
        absensisupirdetail::create(['absensi_id' => '2', 'nobukti' => 'ABS 0002/II/2023', 'trado_id' => '1', 'supir_id' => '0', 'keterangan' => 'STANDARNISASI', 'uangjalan' => '0', 'absen_id' => '6', 'jam' => '00:00:00.0000000', 'modifiedby' => 'ADMIN',]);
        absensisupirdetail::create(['absensi_id' => '2', 'nobukti' => 'ABS 0002/II/2023', 'trado_id' => '6', 'supir_id' => '146', 'keterangan' => '', 'uangjalan' => '100000', 'absen_id' => '0', 'jam' => '00:00:00.0000000', 'modifiedby' => 'ADMIN',]);
        absensisupirdetail::create(['absensi_id' => '2', 'nobukti' => 'ABS 0002/II/2023', 'trado_id' => '7', 'supir_id' => '94', 'keterangan' => '', 'uangjalan' => '125000', 'absen_id' => '0', 'jam' => '00:00:00.0000000', 'modifiedby' => 'ADMIN',]);
        absensisupirdetail::create(['absensi_id' => '2', 'nobukti' => 'ABS 0002/II/2023', 'trado_id' => '8', 'supir_id' => '298', 'keterangan' => '', 'uangjalan' => '100000', 'absen_id' => '0', 'jam' => '00:00:00.0000000', 'modifiedby' => 'ADMIN',]);
        absensisupirdetail::create(['absensi_id' => '2', 'nobukti' => 'ABS 0002/II/2023', 'trado_id' => '11', 'supir_id' => '305', 'keterangan' => '', 'uangjalan' => '100000', 'absen_id' => '0', 'jam' => '00:00:00.0000000', 'modifiedby' => 'ADMIN',]);
        absensisupirdetail::create(['absensi_id' => '2', 'nobukti' => 'ABS 0002/II/2023', 'trado_id' => '12', 'supir_id' => '307', 'keterangan' => '', 'uangjalan' => '100000', 'absen_id' => '0', 'jam' => '00:00:00.0000000', 'modifiedby' => 'ADMIN',]);
        absensisupirdetail::create(['absensi_id' => '2', 'nobukti' => 'ABS 0002/II/2023', 'trado_id' => '14', 'supir_id' => '0', 'keterangan' => 'TIDAK ADA SUPIR', 'uangjalan' => '0', 'absen_id' => '2', 'jam' => '00:00:00.0000000', 'modifiedby' => 'ADMIN',]);
        absensisupirdetail::create(['absensi_id' => '2', 'nobukti' => 'ABS 0002/II/2023', 'trado_id' => '15', 'supir_id' => '171', 'keterangan' => '', 'uangjalan' => '100000', 'absen_id' => '0', 'jam' => '00:00:00.0000000', 'modifiedby' => 'ADMIN',]);
        absensisupirdetail::create(['absensi_id' => '2', 'nobukti' => 'ABS 0002/II/2023', 'trado_id' => '16', 'supir_id' => '72', 'keterangan' => '', 'uangjalan' => '100000', 'absen_id' => '0', 'jam' => '00:00:00.0000000', 'modifiedby' => 'ADMIN',]);
        absensisupirdetail::create(['absensi_id' => '2', 'nobukti' => 'ABS 0002/II/2023', 'trado_id' => '17', 'supir_id' => '0', 'keterangan' => 'TIDAK ADA SUPIR', 'uangjalan' => '0', 'absen_id' => '2', 'jam' => '00:00:00.0000000', 'modifiedby' => 'ADMIN',]);
        absensisupirdetail::create(['absensi_id' => '2', 'nobukti' => 'ABS 0002/II/2023', 'trado_id' => '18', 'supir_id' => '0', 'keterangan' => 'TIDAK ADA SUPIR', 'uangjalan' => '0', 'absen_id' => '2', 'jam' => '00:00:00.0000000', 'modifiedby' => 'ADMIN',]);
        absensisupirdetail::create(['absensi_id' => '2', 'nobukti' => 'ABS 0002/II/2023', 'trado_id' => '19', 'supir_id' => '76', 'keterangan' => '', 'uangjalan' => '100000', 'absen_id' => '0', 'jam' => '00:00:00.0000000', 'modifiedby' => 'ADMIN',]);
        absensisupirdetail::create(['absensi_id' => '2', 'nobukti' => 'ABS 0002/II/2023', 'trado_id' => '22', 'supir_id' => '172', 'keterangan' => '', 'uangjalan' => '100000', 'absen_id' => '0', 'jam' => '00:00:00.0000000', 'modifiedby' => 'ADMIN',]);
        absensisupirdetail::create(['absensi_id' => '2', 'nobukti' => 'ABS 0002/II/2023', 'trado_id' => '24', 'supir_id' => '175', 'keterangan' => '', 'uangjalan' => '100000', 'absen_id' => '0', 'jam' => '00:00:00.0000000', 'modifiedby' => 'ADMIN',]);
        absensisupirdetail::create(['absensi_id' => '2', 'nobukti' => 'ABS 0002/II/2023', 'trado_id' => '25', 'supir_id' => '311', 'keterangan' => '', 'uangjalan' => '100000', 'absen_id' => '0', 'jam' => '00:00:00.0000000', 'modifiedby' => 'ADMIN',]);
        absensisupirdetail::create(['absensi_id' => '2', 'nobukti' => 'ABS 0002/II/2023', 'trado_id' => '27', 'supir_id' => '60', 'keterangan' => '', 'uangjalan' => '100000', 'absen_id' => '0', 'jam' => '00:00:00.0000000', 'modifiedby' => 'ADMIN',]);
        absensisupirdetail::create(['absensi_id' => '2', 'nobukti' => 'ABS 0002/II/2023', 'trado_id' => '28', 'supir_id' => '0', 'keterangan' => 'TIDAK ADA SUPIR', 'uangjalan' => '0', 'absen_id' => '2', 'jam' => '00:00:00.0000000', 'modifiedby' => 'ADMIN',]);
        absensisupirdetail::create(['absensi_id' => '2', 'nobukti' => 'ABS 0002/II/2023', 'trado_id' => '30', 'supir_id' => '10', 'keterangan' => '', 'uangjalan' => '100000', 'absen_id' => '0', 'jam' => '00:00:00.0000000', 'modifiedby' => 'ADMIN',]);
        absensisupirdetail::create(['absensi_id' => '2', 'nobukti' => 'ABS 0002/II/2023', 'trado_id' => '34', 'supir_id' => '0', 'keterangan' => 'TIDAK ADA SUPIR', 'uangjalan' => '0', 'absen_id' => '2', 'jam' => '00:00:00.0000000', 'modifiedby' => 'ADMIN',]);
        absensisupirdetail::create(['absensi_id' => '2', 'nobukti' => 'ABS 0002/II/2023', 'trado_id' => '35', 'supir_id' => '0', 'keterangan' => 'TIDAK ADA SUPIR', 'uangjalan' => '0', 'absen_id' => '2', 'jam' => '00:00:00.0000000', 'modifiedby' => 'ADMIN',]);
        absensisupirdetail::create(['absensi_id' => '2', 'nobukti' => 'ABS 0002/II/2023', 'trado_id' => '38', 'supir_id' => '73', 'keterangan' => '', 'uangjalan' => '100000', 'absen_id' => '0', 'jam' => '00:00:00.0000000', 'modifiedby' => 'ADMIN',]);
        absensisupirdetail::create(['absensi_id' => '2', 'nobukti' => 'ABS 0002/II/2023', 'trado_id' => '39', 'supir_id' => '267', 'keterangan' => '', 'uangjalan' => '100000', 'absen_id' => '0', 'jam' => '00:00:00.0000000', 'modifiedby' => 'ADMIN',]);
        absensisupirdetail::create(['absensi_id' => '2', 'nobukti' => 'ABS 0002/II/2023', 'trado_id' => '40', 'supir_id' => '7', 'keterangan' => '', 'uangjalan' => '100000', 'absen_id' => '0', 'jam' => '00:00:00.0000000', 'modifiedby' => 'ADMIN',]);
        absensisupirdetail::create(['absensi_id' => '2', 'nobukti' => 'ABS 0002/II/2023', 'trado_id' => '42', 'supir_id' => '257', 'keterangan' => '', 'uangjalan' => '100000', 'absen_id' => '0', 'jam' => '00:00:00.0000000', 'modifiedby' => 'ADMIN',]);
    }
}
