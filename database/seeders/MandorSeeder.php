<?php

namespace Database\Seeders;

use App\Models\Mandor;
use Illuminate\Database\Seeder;

class MandorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Mandor::factory()
            ->count(5)
            ->create();
    }
}
