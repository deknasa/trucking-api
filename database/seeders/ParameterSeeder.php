<?php

namespace Database\Seeders;

use App\Models\Parameter;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ParameterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Parameter::factory()
            ->count(100)
            ->create();
    }
}
