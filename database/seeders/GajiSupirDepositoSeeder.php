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
    }
}
