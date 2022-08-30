<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PindahGudangStokDetail;

class PindahGudangStokDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PindahGudangStokDetail::create([
            'pindahgudangstok_id'  => 1,
            'nobukti' => 'PGT 0001/VIII/2022',
            'stok_id'  => 2,
            'conv1' => 1,
            'conv2' => 1,
            'qty'  => 1,
            'vulkanisirke' => '',
            'statusban' => 95,
            'keadaanban' => '',
            'keterangan' => 'PINDAH GUDANG',
            'modifiedby' => 'ADMIN',
        ]);

    }
}
