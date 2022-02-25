<?php

namespace Database\Seeders;
use App\Models\StatusContainer;
use Illuminate\Database\Seeder;

class StatusContainerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        StatusContainer::create([
            'kodestatuscontainer' => 'FULL',
            'keterangan' => 'FULL CONTAINER',
            'statusaktif' => 1,
            'modifiedby' => 'ADMIN',
        ]);

        StatusContainer::create([
            'kodestatuscontainer' => 'EMPTY',
            'keterangan' => 'EMPTY CONTAINER',
            'statusaktif' => 1,
            'modifiedby' => 'ADMIN',
        ]);

        StatusContainer::create([
            'kodestatuscontainer' => 'FULL EMPTY',
            'keterangan' => 'FULL EMPTY CONTAINER',
            'statusaktif' => 1,
            'modifiedby' => 'ADMIN',
        ]);

    }
}
