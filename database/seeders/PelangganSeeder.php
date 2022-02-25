<?php

namespace Database\Seeders;
use App\Models\Pelanggan;
use Illuminate\Database\Seeder;

class PelangganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Pelanggan::create([
            'kodepelanggan' => '3 POWER',
            'namapelanggan' => '3 POWER',
            'keterangan' => '',
            'telp' => '082161573038',
            'alamat' => 'JLN. PERDAMAIAN NO 10',
            'alamat2' => '',
            'kota' => 'MEDAN',
            'kodepos' => '20221',
            'modifiedby' => 'ADMIN',
        ]);
    }
}
