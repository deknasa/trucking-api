<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Bank;
use Illuminate\Support\Facades\DB;

class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete Bank");
        DB::statement("DBCC CHECKIDENT ('Bank', RESEED, 1);");

        Bank::create([  'kodebank' => 'KAS TRUCKING',  'namabank' => 'KAS TRUCKING',  'coa' => '01.01.01.02',  'tipe' => 'KAS',  'formatpenerimaan' => '32',  'formatpengeluaran' => '33',  'statusaktif' => '1',  'modifiedby' => 'ADMIN',  ]);
        Bank::create([  'kodebank' => 'BANK TRUCKING',  'namabank' => 'BANK TRUCKING',  'coa' => '01.02.02.01',  'tipe' => 'BANK',  'formatpenerimaan' => '87',  'formatpengeluaran' => '88',  'statusaktif' => '1',  'modifiedby' => 'ADMIN',  ]);
    }
}
