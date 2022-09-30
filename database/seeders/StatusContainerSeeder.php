<?php

namespace Database\Seeders;

use App\Models\StatusContainer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusContainerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete StatusContainer");
        DB::statement("DBCC CHECKIDENT ('StatusContainer', RESEED, 1);");

        StatusContainer::create(['kodestatuscontainer' => 'FULL', 'keterangan' => 'FULL CONTAINER', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        StatusContainer::create(['kodestatuscontainer' => 'EMPTY', 'keterangan' => 'EMPTY CONTAINER', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        StatusContainer::create(['kodestatuscontainer' => 'FULL EMPTY', 'keterangan' => 'FULL EMPTY CONTAINER', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
    }
}
