<?php

namespace Database\Seeders;

use App\Models\GajiSupirDeposito;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GajiSupirDepositoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete gajisupirdeposito");
        DB::statement("DBCC CHECKIDENT ('gajisupirdeposito', RESEED, 1);");

        gajisupirdeposito::create(['gajisupir_id' => '2', 'gajisupir_nobukti' => 'RIC 0002/II/2023', 'penerimaantrucking_nobukti' => 'DPO 0001/II/2023', 'pengeluarantrucking_nobukti' => '', 'supir_id' => '175', 'nominal' => '25000', 'modifiedby' => 'ADMIN',]);
        gajisupirdeposito::create(['gajisupir_id' => '5', 'gajisupir_nobukti' => 'RIC 0005/II/2023', 'penerimaantrucking_nobukti' => 'DPO 0002/II/2023', 'pengeluarantrucking_nobukti' => '', 'supir_id' => '94', 'nominal' => '25000', 'modifiedby' => 'ADMIN',]);
        gajisupirdeposito::create(['gajisupir_id' => '6', 'gajisupir_nobukti' => 'RIC 0006/II/2023', 'penerimaantrucking_nobukti' => 'DPO 0003/II/2023', 'pengeluarantrucking_nobukti' => '', 'supir_id' => '72', 'nominal' => '25000', 'modifiedby' => 'ADMIN',]);
        gajisupirdeposito::create(['gajisupir_id' => '9', 'gajisupir_nobukti' => 'RIC 0009/II/2023', 'penerimaantrucking_nobukti' => 'DPO 0004/II/2023', 'pengeluarantrucking_nobukti' => '', 'supir_id' => '76', 'nominal' => '25000', 'modifiedby' => 'ADMIN',]);
        gajisupirdeposito::create(['gajisupir_id' => '10', 'gajisupir_nobukti' => 'RIC 0010/II/2023', 'penerimaantrucking_nobukti' => 'DPO 0005/II/2023', 'pengeluarantrucking_nobukti' => '', 'supir_id' => '172', 'nominal' => '25000', 'modifiedby' => 'ADMIN',]);
        gajisupirdeposito::create(['gajisupir_id' => '11', 'gajisupir_nobukti' => 'RIC 0011/II/2023', 'penerimaantrucking_nobukti' => 'DPO 0006/II/2023', 'pengeluarantrucking_nobukti' => '', 'supir_id' => '171', 'nominal' => '25000', 'modifiedby' => 'ADMIN',]);
        gajisupirdeposito::create(['gajisupir_id' => '13', 'gajisupir_nobukti' => 'RIC 0013/II/2023', 'penerimaantrucking_nobukti' => 'DPO 0007/II/2023', 'pengeluarantrucking_nobukti' => '', 'supir_id' => '305', 'nominal' => '25000', 'modifiedby' => 'ADMIN',]);
        gajisupirdeposito::create(['gajisupir_id' => '14', 'gajisupir_nobukti' => 'RIC 0014/II/2023', 'penerimaantrucking_nobukti' => 'DPO 0008/II/2023', 'pengeluarantrucking_nobukti' => '', 'supir_id' => '307', 'nominal' => '25000', 'modifiedby' => 'ADMIN',]);
        gajisupirdeposito::create(['gajisupir_id' => '15', 'gajisupir_nobukti' => 'RIC 0015/II/2023', 'penerimaantrucking_nobukti' => 'DPO 0009/II/2023', 'pengeluarantrucking_nobukti' => '', 'supir_id' => '73', 'nominal' => '5000', 'modifiedby' => 'ADMIN',]);
        gajisupirdeposito::create(['gajisupir_id' => '16', 'gajisupir_nobukti' => 'RIC 0016/II/2023', 'penerimaantrucking_nobukti' => 'DPO 0010/II/2023', 'pengeluarantrucking_nobukti' => '', 'supir_id' => '146', 'nominal' => '50000', 'modifiedby' => 'ADMIN',]);
        gajisupirdeposito::create(['gajisupir_id' => '17', 'gajisupir_nobukti' => 'RIC 0017/II/2023', 'penerimaantrucking_nobukti' => 'DPO 0011/II/2023', 'pengeluarantrucking_nobukti' => '', 'supir_id' => '311', 'nominal' => '75000', 'modifiedby' => 'ADMIN',]);
        gajisupirdeposito::create(['gajisupir_id' => '18', 'gajisupir_nobukti' => 'RIC 0018/II/2023', 'penerimaantrucking_nobukti' => 'DPO 0012/II/2023', 'pengeluarantrucking_nobukti' => '', 'supir_id' => '72', 'nominal' => '5000', 'modifiedby' => 'ADMIN',]);
    }
}
