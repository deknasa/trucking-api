<?php

namespace Database\Seeders;
use App\Models\UpahSupirRincian;
use Illuminate\Database\Seeder;

class UpahSupirRincianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        UpahSupirRincian::create([
            'upahsupir_id' => 1,
            'container_id' => 1,
            'statuscontainer_id' => 1,
            'nominalsupir' => 149760,
            'nominalkenek' => 15000,
            'nominalkomisi' => 0,
            'nominaltol' => 0,
            'liter' => 17.5,
            'modifiedby' => 'ADMIN',
        ]);

        UpahSupirRincian::create([
            'upahsupir_id' => 1,
            'container_id' => 2,
            'statuscontainer_id' => 1,
            'nominalsupir' => 225240,
            'nominalkenek' => 15000,
            'nominalkomisi' => 0,
            'nominaltol' => 0,
            'liter' => 17.5,
            'modifiedby' => 'ADMIN',
        ]);

    }
}
