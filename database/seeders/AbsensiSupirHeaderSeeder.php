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

        AbsensiSupirHeader::create([ 'nobukti' => 'ABS 0001/II/2022', 'tglbukti' => '2022/2/23', 'keterangan' => 'ABSENSI SUPIR TGL 23-02-2022', 'kasgantung_nobukti' => 'KGT 0001/II/2022', 'nominal' => '250000', 'statusformat' => '5', 'modifiedby' => 'ADMIN',]);        
    }
}
