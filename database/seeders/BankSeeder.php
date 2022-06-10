<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Bank;

class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Bank::create([
            'kodebank' => 'KAS TRUCKING',
            'namabank' => 'KAS TRUCKING',
            'coa' => '01.01.01.02',
            'tipe' => 'KAS',
            'statusaktif' => '1',
            'kodepenerimaan' => '32',
            'kodepengeluaran' => '33',
            'modifiedby' => 'ADMIN',
        ]);

        Bank::create([
            'kodebank' => 'BANK TRUCKING',
            'namabank' => 'BANK TRUCKING',
            'coa' => '01.02.02.01',
            'tipe' => 'BANK',
            'statusaktif' => '1',
            'kodepenerimaan' => '87',
            'kodepengeluaran' => '88',
            'modifiedby' => 'ADMIN',
        ]);
    }
}
