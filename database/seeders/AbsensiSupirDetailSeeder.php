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

        AbsensiSupirDetail::create(['absensi_id' => '1', 'nobukti' => 'ABS 0001/XII/2022', 'trado_id' => '1', 'supir_id' => '1', 'keterangan' => 'ABSENSI -1', 'uangjalan' => '250000', 'absen_id' => '0', 'jam' => '11:11:00.0000000', 'modifiedby' => 'ADMIN',]);
    }
}
