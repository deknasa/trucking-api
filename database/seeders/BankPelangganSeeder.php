<?php

namespace Database\Seeders;

use App\Models\BankPelanggan;

use Illuminate\Database\Seeder;

class BankPelangganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        BankPelanggan::create([
            'kodebank' => 'BCA',
            'namabank' => 'BANK CENTRAL ASIA',
            'keterangan' => '-',
            'statusaktif' => '1',
            'modifiedby' => 'ADMIN',
        ]);
    }
}
