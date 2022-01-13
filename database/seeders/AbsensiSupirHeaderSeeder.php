<?php

namespace Database\Seeders;

use App\Models\AbsensiSupirHeader;
use Illuminate\Database\Seeder;

class AbsensiSupirHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        AbsensiSupirHeader::factory()
            ->count(100)
            ->create();
    }
}
