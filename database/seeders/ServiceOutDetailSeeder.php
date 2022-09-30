<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ServiceOutDetail;
use Illuminate\Support\Facades\DB;


class ServiceOutDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete ServiceOutDetail");
        DB::statement("DBCC CHECKIDENT ('ServiceOutDetail', RESEED, 1);");

        ServiceOutDetail::create(['serviceout_id' => '1', 'nobukti' => 'SOUT 0001/V/2022', 'servicein_nobukti' => 'SIN 0001/V/2022', 'keterangan' => 'SERVICE OPNAME', 'modifiedby' => 'ADMIN',]);
    }
}
