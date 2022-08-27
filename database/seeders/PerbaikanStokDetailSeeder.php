<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PerbaikanStokDetail;

class PerbaikanStokDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PerbaikanStokDetail::create([
            'perbaikanstok_id' => 1,
            'nobukti' => 'PST 0001/VIII/2022',
            'stok_id' => 2,
            'conv1' => 1,
            'conv2' => 1,
            'qty' => 1,
            'hrgsat' => 200000,
            'persentasediscount' => 0,
            'nominaldiscount' => 0,
            'total' => 200000,
            'keterangan' => 'PERBAIKAN RADIATOR',
            'gudang_id' => 3,
            'jenisvulkan' => '',
            'vulkanisirke' => '',
            'statusban' => 95,
            'pindahgudangstok_nobukti' => 'DOT 0001/VIiI/2022',
            'vulkankeawal' => '',
            'statuspindahgudang' => 104,
            'modifiedby' => 'ADMIN',
        ]);

   

    }
}
