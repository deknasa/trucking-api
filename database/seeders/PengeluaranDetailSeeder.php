<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PengeluaranDetail;
use Illuminate\Support\Facades\DB;

class PengeluaranDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {



        DB::statement("delete pengeluarandetail");
        DB::statement("DBCC CHECKIDENT ('pengeluarandetail', RESEED, 1);");

        pengeluarandetail::create(['pengeluaran_id' => '1', 'nobukti' => 'KBT 0001/II/2023', 'nowarkat' => '', 'tgljatuhtempo' => '1970/1/1', 'nominal' => '19645', 'coadebet' => '01.05.02.02', 'coakredit' => '01.01.01.02', 'keterangan' => 'GAJI MINUS SUPIR CHANDRA BK 8743 BU TGL. 01 FEBRUARI 2023', 'bulanbeban' => '1900/1/1', 'modifiedby' => 'ADMIN',]);
        pengeluarandetail::create(['pengeluaran_id' => '2', 'nobukti' => 'KBT 0002/II/2023', 'nowarkat' => '', 'tgljatuhtempo' => '1970/1/1', 'nominal' => '100780', 'coadebet' => '01.05.02.02', 'coakredit' => '01.01.01.02', 'keterangan' => 'GAJI MINUS SUPIR ERIKSON BK 8264 FB TGL. 01 FEBRUARI 2023', 'bulanbeban' => '1900/1/1', 'modifiedby' => 'ADMIN',]);
        pengeluarandetail::create(['pengeluaran_id' => '3', 'nobukti' => 'KBT 0003/II/2023', 'nowarkat' => '', 'tgljatuhtempo' => '1970/1/1', 'nominal' => '33408', 'coadebet' => '01.05.02.02', 'coakredit' => '01.01.01.02', 'keterangan' => 'GAJI MINUS SUPIR SAHBUDIN  BK 8178 EW TGL. 01 FEBRUARI 2023', 'bulanbeban' => '1900/1/1', 'modifiedby' => 'ADMIN',]);
        pengeluarandetail::create(['pengeluaran_id' => '4', 'nobukti' => 'KBT 0004/II/2023', 'nowarkat' => '', 'tgljatuhtempo' => '1970/1/1', 'nominal' => '215736', 'coadebet' => '01.05.02.02', 'coakredit' => '01.01.01.02', 'keterangan' => 'GAJI MINUS SUPIR SULAIMAN B 9668 QZ TGL. 01 FEBRUARI 2023', 'bulanbeban' => '1900/1/1', 'modifiedby' => 'ADMIN',]);
    }
}
