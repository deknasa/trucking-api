<?php

namespace Database\Seeders;

use App\Models\AbsenTrado;
use Illuminate\Database\Seeder;

class AbsenTradoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        AbsenTrado::factory()
            ->count(3)
            ->create();
    }
}
