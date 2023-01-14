<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ServiceOutHeader;
use Illuminate\Support\Facades\DB;

class ServiceOutHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
  
        DB::statement("delete ServiceOutHeader");
        DB::statement("DBCC CHECKIDENT ('ServiceOutHeader', RESEED, 1);");

        ServiceOutHeader::create([ 'nobukti' => 'SOUT 0001/V/2022', 'tglbukti' => '2022/5/31', 'trado_id' => '1', 'tglkeluar' => '2022/5/31',  'statusformat' => '143', 'modifiedby' => 'ADMIN',]);

    }
}
