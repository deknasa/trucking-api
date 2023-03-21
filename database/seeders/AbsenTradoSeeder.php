<?php

namespace Database\Seeders;

use App\Models\AbsenTrado;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AbsenTradoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete AbsenTrado");
        DB::statement("DBCC CHECKIDENT ('AbsenTrado',RESEED, 1);");

        absentrado::create(['kodeabsen' => 'I', 'keterangan' => 'INAP', 'statusaktif' => '1',  'memo' => '{"MEMO":"INAP","SINGKATAN":"I","WARNA":"#28A745"}', 'modifiedby' => 'ADMIN',]);
        absentrado::create(['kodeabsen' => 'TS', 'keterangan' => 'TIDAK ADA SUPIR', 'statusaktif' => '1', 'memo' => '{"MEMO":"TIDAK ADA SUPIR","SINGKATAN":"TS","WARNA":"#28A745"}', 'modifiedby' => 'ADMIN',]);
        absentrado::create(['kodeabsen' => 'K', 'keterangan' => 'KECELAKAAN', 'statusaktif' => '1',  'memo' => '{"MEMO":"KECELAKAAN","SINGKATAN":"K","WARNA":"#28A745"}', 'modifiedby' => 'ADMIN',]);
        absentrado::create(['kodeabsen' => 'L', 'keterangan' => 'LIBUR/MINGGU', 'statusaktif' => '1', 'memo' => '{"MEMO":"LIBUR/MINGGU","SINGKATAN":"L","WARNA":"#28A745"}', 'modifiedby' => 'ADMIN',]);
        absentrado::create(['kodeabsen' => 'M', 'keterangan' => 'TDK ADA MUATAN/TRUCK DALAM KONDISI SEHAT', 'statusaktif' => '1',  'memo' => '{"MEMO":"TDK ADA MUATAN/TRUCK DALAM KONDISI SEHAT","SINGKATAN":"M","WARNA":"#28A745"}', 'modifiedby' => 'ADMIN',]);
        absentrado::create(['kodeabsen' => 'R', 'keterangan' => 'TRUCK DALAM PERBAIKAN/TDK BISA JALAN', 'statusaktif' => '1',  'memo' => '{"MEMO":"TRUCK DALAM PERBAIKAN/TDK BISA JALAN","SINGKATAN":"R","WARNA":"#28A745"}', 'modifiedby' => 'ADMIN',]);
        absentrado::create(['kodeabsen' => 'S', 'keterangan' => 'SUPIR ABSEN', 'statusaktif' => '1',  'memo' => '{"MEMO":"SUPIR ABSEN","SINGKATAN":"S","WARNA":"#28A745"}', 'modifiedby' => 'ADMIN',]);
    }
}
