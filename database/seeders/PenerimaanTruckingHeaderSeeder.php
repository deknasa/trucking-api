<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PenerimaanTruckingHeader;
use Illuminate\Support\Facades\DB;

class PenerimaanTruckingHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete penerimaantruckingheader");
        DB::statement("DBCC CHECKIDENT ('penerimaantruckingheader', RESEED, 1);");

        penerimaantruckingheader::create(['nobukti' => 'BBM 0001/II/2023', 'tglbukti' => '2023/2/1', 'keterangan' => '', 'penerimaantrucking_id' => '1', 'bank_id' => '0', 'coa' => '01.09.01.06', 'penerimaan_nobukti' => '', 'statusformat' => '265', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
        penerimaantruckingheader::create(['nobukti' => 'DPO 0001/II/2023', 'tglbukti' => '2023/2/1', 'keterangan' => '', 'penerimaantrucking_id' => '3', 'bank_id' => '0', 'coa' => '01.04.02.01', 'penerimaan_nobukti' => '', 'statusformat' => '125', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
        penerimaantruckingheader::create(['nobukti' => 'BBM 0002/II/2023', 'tglbukti' => '2023/2/1', 'keterangan' => '', 'penerimaantrucking_id' => '1', 'bank_id' => '0', 'coa' => '01.09.01.06', 'penerimaan_nobukti' => '', 'statusformat' => '265', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
        penerimaantruckingheader::create(['nobukti' => 'BBM 0003/II/2023', 'tglbukti' => '2023/2/1', 'keterangan' => '', 'penerimaantrucking_id' => '1', 'bank_id' => '0', 'coa' => '01.09.01.06', 'penerimaan_nobukti' => '', 'statusformat' => '265', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
    }
}
