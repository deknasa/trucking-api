<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KoreksiStokDetail;

class KoreksiStokDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        KoreksiStokDetail::create([
            'nobukti' => 'KST 0001/VIII/2022',
            'koreksistok_id' => 1,
            'stok_id' => 1,
            'conv1' => 1,
            'conv2' => 1,
            'qty' => 2,
            'hrgsatuan' => 500,  
            'total'   => 1000,
            'keterangan' => 'OPNAME STOCK',
            'modifiedby' => 'ADMIN',
        ]);

    }
}
