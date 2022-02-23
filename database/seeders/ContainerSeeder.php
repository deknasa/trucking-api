<?php

namespace Database\Seeders;
use App\Models\Container;

use Illuminate\Database\Seeder;

class ContainerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Container::create([
            'kodecontainer' => '20`',
            'keterangan' => 'CONTAINER 20 FEET',
            'statusaktif' => 1,
            'modifiedby' => 'ADMIN',
        ]);

        Container::create([
            'kodecontainer' => '40`',
            'keterangan' => 'CONTAINER 40 FEET',
            'statusaktif' => 1,
            'modifiedby' => 'ADMIN',
        ]);        

        Container::create([
            'kodecontainer' => '2X20`',
            'keterangan' => 'CONTAINER 2X20 FEET',
            'statusaktif' => 1,
            'modifiedby' => 'ADMIN',
        ]);        

    }
}
