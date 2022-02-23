<?php

namespace Database\Seeders;
use App\Models\Merk;
use Illuminate\Database\Seeder;

class MerkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Merk::create([
            'kodemerk' => 'INDOPART',
            'keterangan' => 'MERK INDOPART',
            'statusaktif' => 1,
            'modifiedby' => 'ADMIN',
        ]);
    }
}
