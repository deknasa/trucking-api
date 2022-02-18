<?php

namespace Database\Seeders;

use App\Models\AkunPusat;
use Illuminate\Database\Seeder;

class AkunPusatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
            AkunPusat::factory()->create();
    }
}
