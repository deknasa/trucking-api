<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ServiceInDetail;
use Illuminate\Support\Facades\DB;

class ServiceInDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete ServiceInDetail");
        DB::statement("DBCC CHECKIDENT ('ServiceInDetail', RESEED, 1);");

        ServiceInDetail::create(['servicein_id' => '1', 'nobukti' => 'SIN 0001/V/2022', 'mekanik_id' => '1', 'keterangan' => 'SERVICE OPNAME', 'modifiedby' => 'ADMIN',]);
    }
}
