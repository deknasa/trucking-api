<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PenerimaanTruckingDetail;
use Illuminate\Support\Facades\DB;

class PenerimaanTruckingDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete penerimaantruckingdetail");
        DB::statement("DBCC CHECKIDENT ('penerimaantruckingdetail', RESEED, 1);");
        
        penerimaantruckingdetail::create([ 'penerimaantruckingheader_id' => '1', 'nobukti' => 'BBM 0001/II/2023', 'supir_id' => '60', 'pengeluarantruckingheader_nobukti' => '', 'nominal' => '280534', 'keterangan' => 'BBM SUPIR', 'modifiedby' => 'ADMIN',]);
        penerimaantruckingdetail::create([ 'penerimaantruckingheader_id' => '2', 'nobukti' => 'DPO 0001/II/2023', 'supir_id' => '175', 'pengeluarantruckingheader_nobukti' => '', 'nominal' => '25000', 'keterangan' => 'DEPOSITO', 'modifiedby' => 'ADMIN',]);
        penerimaantruckingdetail::create([ 'penerimaantruckingheader_id' => '3', 'nobukti' => 'BBM 0002/II/2023', 'supir_id' => '175', 'pengeluarantruckingheader_nobukti' => '', 'nominal' => '418812', 'keterangan' => 'BBM', 'modifiedby' => 'ADMIN',]);
        penerimaantruckingdetail::create([ 'penerimaantruckingheader_id' => '4', 'nobukti' => 'BBM 0003/II/2023', 'supir_id' => '83', 'pengeluarantruckingheader_nobukti' => '', 'nominal' => '270028', 'keterangan' => 'BBM', 'modifiedby' => 'ADMIN',]);
    }
}
