<?php

namespace Database\Seeders;

use App\Models\Karyawan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KaryawanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete Kota");
        DB::statement("DBCC CHECKIDENT ('Kota', RESEED, 1);");

        karyawan::create(['namakaryawan' => 'DONAL T SINAGA', 'keterangan' => 'DONAL T SINAGA', 'statusaktif' => '1', 'statusstaff' => '280', 'modifiedby' => 'ADMIN',]);
        karyawan::create(['namakaryawan' => 'JOKO SUKIRNO', 'keterangan' => 'JOKO SUKIRNO', 'statusaktif' => '1', 'statusstaff' => '280', 'modifiedby' => 'ADMIN',]);
        karyawan::create(['namakaryawan' => 'NG BIN SAN', 'keterangan' => 'NG BIN SAN', 'statusaktif' => '1', 'statusstaff' => '280', 'modifiedby' => 'ADMIN',]);
    }
}
