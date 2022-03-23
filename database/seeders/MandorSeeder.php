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
        Mandor::create([
            'namamandor' => 'ASAN',
            'keterangan' => 'PENGURUS TRUCKING',
            'statusaktif' => 1,
            'modifiedby' => 'ADMIN',
        ]);
    }
}
