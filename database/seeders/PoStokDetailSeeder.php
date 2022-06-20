<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PoStokDetail;

class PoStokDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PoStokDetail::create([
            'postok_id'  => 1,
            'nobukti' => 'POT 0001/VI/2022',
            'stok_id'  => 1,
            'conv1' => 1,
            'conv2' => 1,
            'statusstok' => 2,
            'qty'  => 10,
            'hrgsatuan'  => 500,
            'total'  => 5000,
            'keterangan' =>'PEMBELIAN BAUT',
            'modifiedby' => 'ADMIN',

        ]);
    }
}
