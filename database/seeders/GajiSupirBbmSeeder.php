<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GajiSupirBbm;
use Illuminate\Support\Facades\DB;

class GajiSupirBbmSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete GajiSupirBbm");
        DB::statement("DBCC CHECKIDENT ('GajiSupirBbm', RESEED, 1);");

        gajisupirbbm::create(['gajisupir_id' => '1', 'gajisupir_nobukti' => 'RIC 0001/II/2023', 'penerimaantrucking_nobukti' => 'BBM 0001/II/2023', 'pengeluarantrucking_nobukti' => '', 'supir_id' => '60', 'nominal' => '280534', 'modifiedby' => 'ADMIN',]);
        gajisupirbbm::create(['gajisupir_id' => '2', 'gajisupir_nobukti' => 'RIC 0002/II/2023', 'penerimaantrucking_nobukti' => 'BBM 0002/II/2023', 'pengeluarantrucking_nobukti' => '', 'supir_id' => '175', 'nominal' => '418812', 'modifiedby' => 'ADMIN',]);
        gajisupirbbm::create(['gajisupir_id' => '3', 'gajisupir_nobukti' => 'RIC 0003/II/2023', 'penerimaantrucking_nobukti' => 'BBM 0003/II/2023', 'pengeluarantrucking_nobukti' => '', 'supir_id' => '83', 'nominal' => '270028', 'modifiedby' => 'ADMIN',]);
        gajisupirbbm::create(['gajisupir_id' => '5', 'gajisupir_nobukti' => 'RIC 0005/II/2023', 'penerimaantrucking_nobukti' => 'BBM 0004/II/2023', 'pengeluarantrucking_nobukti' => '', 'supir_id' => '94', 'nominal' => '533868', 'modifiedby' => 'ADMIN',]);
        gajisupirbbm::create(['gajisupir_id' => '6', 'gajisupir_nobukti' => 'RIC 0006/II/2023', 'penerimaantrucking_nobukti' => 'BBM 0005/II/2023', 'pengeluarantrucking_nobukti' => '', 'supir_id' => '72', 'nominal' => '311032', 'modifiedby' => 'ADMIN',]);
        gajisupirbbm::create(['gajisupir_id' => '7', 'gajisupir_nobukti' => 'RIC 0007/II/2023', 'penerimaantrucking_nobukti' => 'BBM 0006/II/2023', 'pengeluarantrucking_nobukti' => '', 'supir_id' => '73', 'nominal' => '500140', 'modifiedby' => 'ADMIN',]);
        gajisupirbbm::create(['gajisupir_id' => '8', 'gajisupir_nobukti' => 'RIC 0008/II/2023', 'penerimaantrucking_nobukti' => 'BBM 0007/II/2023', 'pengeluarantrucking_nobukti' => '', 'supir_id' => '267', 'nominal' => '545020', 'modifiedby' => 'ADMIN',]);
        gajisupirbbm::create(['gajisupir_id' => '9', 'gajisupir_nobukti' => 'RIC 0009/II/2023', 'penerimaantrucking_nobukti' => 'BBM 0008/II/2023', 'pengeluarantrucking_nobukti' => '', 'supir_id' => '76', 'nominal' => '516596', 'modifiedby' => 'ADMIN',]);
        gajisupirbbm::create(['gajisupir_id' => '10', 'gajisupir_nobukti' => 'RIC 0010/II/2023', 'penerimaantrucking_nobukti' => 'BBM 0009/II/2023', 'pengeluarantrucking_nobukti' => '', 'supir_id' => '172', 'nominal' => '396032', 'modifiedby' => 'ADMIN',]);
        gajisupirbbm::create(['gajisupir_id' => '11', 'gajisupir_nobukti' => 'RIC 0011/II/2023', 'penerimaantrucking_nobukti' => 'BBM 0010/II/2023', 'pengeluarantrucking_nobukti' => '', 'supir_id' => '171', 'nominal' => '480080', 'modifiedby' => 'ADMIN',]);
        gajisupirbbm::create(['gajisupir_id' => '12', 'gajisupir_nobukti' => 'RIC 0012/II/2023', 'penerimaantrucking_nobukti' => 'BBM 0011/II/2023', 'pengeluarantrucking_nobukti' => '', 'supir_id' => '298', 'nominal' => '530400', 'modifiedby' => 'ADMIN',]);
        gajisupirbbm::create(['gajisupir_id' => '13', 'gajisupir_nobukti' => 'RIC 0013/II/2023', 'penerimaantrucking_nobukti' => 'BBM 0012/II/2023', 'pengeluarantrucking_nobukti' => '', 'supir_id' => '305', 'nominal' => '960024', 'modifiedby' => 'ADMIN',]);
        gajisupirbbm::create(['gajisupir_id' => '14', 'gajisupir_nobukti' => 'RIC 0014/II/2023', 'penerimaantrucking_nobukti' => 'BBM 0013/II/2023', 'pengeluarantrucking_nobukti' => '', 'supir_id' => '307', 'nominal' => '405008', 'modifiedby' => 'ADMIN',]);
        gajisupirbbm::create(['gajisupir_id' => '16', 'gajisupir_nobukti' => 'RIC 0016/II/2023', 'penerimaantrucking_nobukti' => 'BBM 0014/II/2023', 'pengeluarantrucking_nobukti' => '', 'supir_id' => '146', 'nominal' => '760104', 'modifiedby' => 'ADMIN',]);
        gajisupirbbm::create(['gajisupir_id' => '17', 'gajisupir_nobukti' => 'RIC 0017/II/2023', 'penerimaantrucking_nobukti' => 'BBM 0015/II/2023', 'pengeluarantrucking_nobukti' => '', 'supir_id' => '311', 'nominal' => '620024', 'modifiedby' => 'ADMIN',]);
    }
}
