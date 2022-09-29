<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ServiceInHeader;
use Illuminate\Support\Facades\DB;

class ServiceInHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete ServiceInHeader");
        DB::statement("DBCC CHECKIDENT ('ServiceInHeader', RESEED, 1);");

        ServiceInHeader::create([ 'nobukti' => 'SIN 0001/V/2022', 'tglbukti' => '2022/5/31', 'trado_id' => '1', 'tglmasuk' => '2022/5/30', 'keterangan' => 'SERVICE OPNAME', 'statusformat' => '142', 'modifiedby' => 'ADMIN',]);
    }
}
