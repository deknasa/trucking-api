<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SpkStokHeader;

class SpkStokHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SpkStokHeader::create([
            'nobukti' => 'SPK 0001/VIII/2022',
            'tglbukti' => '2022/8/15',
            'trado_id' => 1,
            'gudang_id' => 1,
            'supir_id' => 1,
            'pg_nobukti' => '',
            'editke' => 0,
            'sin_nobukti' => '',
            'kerusakan_id' => 1,
            'keterangan' => 'GANTI YANG RUSAK',
            'modifiedby' => 'ADMIN',
        ]);
    }
}
