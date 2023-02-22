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

        tarif::create([ 'parent_id' => '0', 'upahsupir_id' => '0', 'tujuan' => 'AMPLAS', 'statusaktif' => '1', 'statussistemton' => '41', 'kota_id' => '2', 'zona_id' => '0', 'tglmulaiberlaku' => '2023/2/21', 'statuspenyesuaianharga' => '43', 'modifiedby' => 'ADMIN',]);
    }
}
