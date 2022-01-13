<?php

namespace Database\Seeders;

use App\Models\Trado;
use Illuminate\Database\Seeder;

class TradoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Trado::factory()
            ->count(5)
            ->create();
    }
}
