<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PengeluaranTruckingHeader;
use Illuminate\Support\Facades\DB;


class PengeluaranTruckingHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       
        DB::statement("delete pengeluarantruckingheader");
        DB::statement("DBCC CHECKIDENT ('pengeluarantruckingheader', RESEED, 1);");

        pengeluarantruckingheader::create([ 'nobukti' => 'PJT 0001/II/2023', 'tglbukti' => '2023/2/1', 'pengeluarantrucking_id' => '1', 'bank_id' => '1', 'statusposting' => '84', 'coa' => '01.05.02.02', 'pengeluaran_nobukti' => 'KBT 0001/II/2023', 'statusformat' => '122', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
        pengeluarantruckingheader::create([ 'nobukti' => 'PJT 0002/II/2023', 'tglbukti' => '2023/2/1', 'pengeluarantrucking_id' => '1', 'bank_id' => '1', 'statusposting' => '84', 'coa' => '01.05.02.02', 'pengeluaran_nobukti' => 'KBT 0002/II/2023', 'statusformat' => '122', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
        pengeluarantruckingheader::create([ 'nobukti' => 'PJT 0003/II/2023', 'tglbukti' => '2023/2/1', 'pengeluarantrucking_id' => '1', 'bank_id' => '1', 'statusposting' => '84', 'coa' => '01.05.02.02', 'pengeluaran_nobukti' => 'KBT 0003/II/2023', 'statusformat' => '122', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
        pengeluarantruckingheader::create([ 'nobukti' => 'PJT 0004/II/2023', 'tglbukti' => '2023/2/1', 'pengeluarantrucking_id' => '1', 'bank_id' => '1', 'statusposting' => '84', 'coa' => '01.05.02.02', 'pengeluaran_nobukti' => 'KBT 0004/II/2023', 'statusformat' => '122', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);

    }
}
