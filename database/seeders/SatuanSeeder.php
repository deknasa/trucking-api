<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Satuan;

class SatuanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Satuan::create([
            'satuan' => 'PCS',
            'statusaktif' => 1,
            'modifiedby' => 'ADMIN',
        ]);
    }
}
