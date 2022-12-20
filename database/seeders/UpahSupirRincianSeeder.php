<?php

namespace Database\Seeders;

use App\Models\UpahSupirRincian;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpahSupirRincianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete UpahSupirrincian");
        DB::statement("DBCC CHECKIDENT ('UpahSupirrincian', RESEED, 1);");

        UpahSupirrincian::create(['upahsupir_id' => '1', 'container_id' => '1', 'statuscontainer_id' => '1', 'nominalsupir' => '500000', 'nominalkenek' => '0', 'nominalkomisi' => '0', 'nominaltol' => '0', 'liter' => '0', 'modifiedby' => 'ADMIN',]);
        UpahSupirrincian::create(['upahsupir_id' => '1', 'container_id' => '1', 'statuscontainer_id' => '2', 'nominalsupir' => '200000', 'nominalkenek' => '0', 'nominalkomisi' => '0', 'nominaltol' => '0', 'liter' => '0', 'modifiedby' => 'ADMIN',]);
    }
}
