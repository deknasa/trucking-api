<?php

namespace Database\Seeders;

use App\Models\AbsensiSupirDetail;
use Illuminate\Database\Seeder;

class AbsensiSupirDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        AbsensiSupirDetail::factory()
            ->count(200)
            ->create();
    }
}
