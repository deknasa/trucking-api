<?php

namespace Database\Seeders;
use App\Models\JenisEmkl;
use Illuminate\Database\Seeder;

class JenisEmklSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        JenisEmkl::create([
            'kodejenisemkl' => 'TAS',
            'keterangan' => 'EMKL TAS',
            'modifiedby' => 'ADMIN',
        ]);

        JenisEmkl::create([
            'kodejenisemkl' => 'OL',
            'keterangan' => 'ORDERAN LUAR',
            'modifiedby' => 'ADMIN',
        ]);

    }
}
