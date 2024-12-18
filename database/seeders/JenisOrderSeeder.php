<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JenisOrder;
use Illuminate\Support\Facades\DB;

class JenisOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete JenisOrder");
        DB::statement("DBCC CHECKIDENT ('JenisOrder', RESEED, 1);");

        jenisorder::create(['kodejenisorder' => 'MUAT', 'keterangan' => 'MUATAN', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
        jenisorder::create(['kodejenisorder' => 'BKR', 'keterangan' => 'BONGKARAN', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
        jenisorder::create(['kodejenisorder' => 'IMP', 'keterangan' => 'IMPORT', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
        jenisorder::create(['kodejenisorder' => 'EKS', 'keterangan' => 'EKSPORT', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
    }
}
