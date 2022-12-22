<?php

namespace Database\Seeders;

use App\Models\OrderanTrucking;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderanTruckingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete orderantrucking");
        DB::statement("DBCC CHECKIDENT ('orderantrucking', RESEED, 1);");

        orderantrucking::create(['nobukti' => '0001/XII/2022', 'tglbukti' => '2022/12/20', 'container_id' => '1', 'agen_id' => '2', 'jenisorder_id' => '1', 'pelanggan_id' => '1', 'tarif_id' => '1', 'nominal' => '1021000', 'nojobemkl' => '111', 'nocont' => '111', 'noseal' => '111', 'nojobemkl2' => '', 'nocont2' => '', 'noseal2' => '', 'statuslangsir' => '80', 'statusperalihan' => '68', 'statusformat' => '103', 'modifiedby' => 'ADMIN',]);
        orderantrucking::create(['nobukti' => '0002/XII/2022', 'tglbukti' => '2022/12/22', 'container_id' => '1', 'agen_id' => '1', 'jenisorder_id' => '1', 'pelanggan_id' => '1', 'tarif_id' => '1', 'nominal' => '1021000', 'nojobemkl' => 'PRE1/II/KTR - MD/JKT/22', 'nocont' => 'CONT 24912', 'noseal' => 'SEAL 40129', 'nojobemkl2' => '', 'nocont2' => '', 'noseal2' => '', 'statuslangsir' => '80', 'statusperalihan' => '68', 'statusformat' => '103', 'modifiedby' => 'ADMIN',]);
    }
}
