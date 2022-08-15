<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ReturBeliStokDetail;

class ReturBeliStokDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ReturBeliStokDetail::create([
            'nobukti' => 'RBT 0001/VII/2022',
            'returbelistok_id' => 1,
            'stok_id' => 1,
            'gudang_id' => 1,
            'conv1' => 1,
            'conv2' => 1,
            'qty' => 1,
            'hrgsatuan' => 500,            
            'persentasediscount' => 0,            
            'nominaldiscount' => 0,            
            'total' => 0,            
            'keterangan' => 'RETUR PEMBELIAN BAUT',
            'modifiedby' => 'ADMIN',


        ]);
    }
}
