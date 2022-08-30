<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SpkStokDetail;

class SpkStokDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SpkStokDetail::create([
            'nobukti' => 'SPK 0001/VIII/2022',
            'spkstok_id' => 1,
            'stok_id' => 1,
            'conv1' => 1,
            'conv2' => 1,
            'qty' => 2,
            'hrgsatuan' => 500,  
            'total' => 1000,  
            'coa' => '07.03.01.03',
            'statusoli' => '',
            'vulke' => '',
            'statusban' => '',
            'kodebanasal' => '',
            'jenisvulkanisir' => '',
            'pindahgudang_nobukti' => '',
            'keadaanban' => '',
            'keterangan' => 'PEMAKAIAN BARANG',
            'modifiedby' => 'ADMIN',

        ]);
    }
}
