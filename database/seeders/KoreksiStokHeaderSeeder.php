<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KoreksiStokHeader;

class KoreksiStokHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //

        KoreksiStokHeader::create([
            'nobukti' => 'KST 0001/VIII/2022',
            'tglbukti' => '2022/8/15',
            'keterangan' => 'OPNAME STOCK',
            'trado_id' => 1,
            'gudang_id' => 1,
            'modifiedby' => 'ADMIN',
        ]);
    }
}
