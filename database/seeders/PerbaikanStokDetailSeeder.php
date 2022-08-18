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
            'perbaikanstok_id' => '',
            'nobukti' => '',
            'stok_id' => '',
            'conv1' => '',
            'conv2' => '',
            'qty' => '',
            'hrgsat' => '',
            'persentasediscount' => '',
            'nominaldiscount' => '',
            'total' => '',
            'keterangan' => '',
            'gudang_id' => '',
            'jenisvulkan' => '',
            'vulkanisirke' => '',
            'statusban' => '',
            'pindahgudangstok_nobukti' => '',
            'vulkankeawal' => '',
            'statuspindahgudang' => '',
            'modifiedby' => 'ADMIN',
        ]);

    }
}
