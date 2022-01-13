<?php

namespace Database\Seeders;

use App\Models\Supir;
use Illuminate\Database\Seeder;

class SupirSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Supir::factory()
            ->count(10)
            ->create();
    }
}
