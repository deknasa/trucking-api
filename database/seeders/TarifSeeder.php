<?php

namespace Database\Seeders;

use App\Models\Tarif;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TarifSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete Tarif");
        DB::statement("DBCC CHECKIDENT ('Tarif', RESEED, 1);");
        // Tarif::create(['tujuan' => 'AMPLAS', 'container_id' => '1', 'nominal' => '1021000', 'statusaktif' => '1', 'statussistemton' => '40', 'kota_id' => '1', 'zona_id' => '1', 'nominalton' => '0', 'tglmulaiberlaku' => '2022/1/1',  'statuspenyesuaianharga' => '43', 'modifiedby' => 'ADMIN',]);
        // Tarif::create(['tujuan' => 'AMPLAS', 'container_id' => '2', 'nominal' => '1463000', 'statusaktif' => '1', 'statussistemton' => '40', 'kota_id' => '1', 'zona_id' => '1', 'nominalton' => '0', 'tglmulaiberlaku' => '2022/1/1',  'statuspenyesuaianharga' => '43', 'modifiedby' => 'ADMIN',]);
    }
}
