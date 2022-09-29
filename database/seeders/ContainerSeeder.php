<?php

namespace Database\Seeders;

use App\Models\Container;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContainerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete Container");
        DB::statement("DBCC CHECKIDENT ('Container', RESEED, 1);");
        
        container::create(['kodecontainer' => '20`', 'keterangan' => '20`', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        container::create(['kodecontainer' => '40`', 'keterangan' => '40`', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
    }
}
