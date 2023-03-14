<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PengeluaranTruckingDetail;
use Illuminate\Support\Facades\DB;

class PengeluaranTruckingDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete pengeluarantruckingdetail");
        DB::statement("DBCC CHECKIDENT ('pengeluarantruckingdetail', RESEED, 1);");

        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '1', 'nobukti' => 'PJT 0001/II/2023', 'supir_id' => '60', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '19645', 'keterangan' => 'GAJI MINUS SUPIR CHANDRA BK 8743 BU TGL. 01 FEBRUARI 2023', 'modifiedby' => 'ADMIN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '2', 'nobukti' => 'PJT 0002/II/2023', 'supir_id' => '83', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '100780', 'keterangan' => 'GAJI MINUS SUPIR ERIKSON BK 8264 FB TGL. 01 FEBRUARI 2023', 'modifiedby' => 'ADMIN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '3', 'nobukti' => 'PJT 0003/II/2023', 'supir_id' => '267', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '33408', 'keterangan' => 'GAJI MINUS SUPIR SAHBUDIN  BK 8178 EW TGL. 01 FEBRUARI 2023', 'modifiedby' => 'ADMIN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '4', 'nobukti' => 'PJT 0004/II/2023', 'supir_id' => '298', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '215736', 'keterangan' => 'GAJI MINUS SUPIR SULAIMAN B 9668 QZ TGL. 01 FEBRUARI 2023', 'modifiedby' => 'ADMIN',]);
    }
}
