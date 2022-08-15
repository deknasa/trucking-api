<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BeliStokDetail;

class BeliStokDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        BeliStokDetail::create([
            'belistok_id' => 1,            
            'nobukti' => 'PBT 0001/VII/2022',
            'stok_id' => 1,            
            'conv1' => 1,
            'conv2' => 1,
            'qty0' => 0,
            'qty1' => 0,
            'qty2' => 10,
            'totalqty' => 10,
            'harga0' => 0,
            'harga1' => 0,
            'harga2' => 500,
            'persentasediscount' => 0,
            'nominaldiscount' => 0,
            'total' => 5000,
            'keterangan' => 'PEMBELIAN BARANG',
            'gudang_id' => 1,            
            'modifiedby' => 'ADMIN',
        ]);
    }
}
