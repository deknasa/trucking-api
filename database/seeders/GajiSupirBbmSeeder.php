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
    }
}
