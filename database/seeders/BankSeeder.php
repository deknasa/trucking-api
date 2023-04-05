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

        Bank::create([  'kodebank' => 'KAS TRUCKING',  'namabank' => 'KAS TRUCKING',  'coa' => '01.01.01.02',  'tipe' => 'KAS',  'formatpenerimaan' => '32',  'formatpengeluaran' => '33',  'statusdefault' => '58',  'statusaktif' => '1',  'modifiedby' => 'ADMIN',  ]);
        Bank::create([  'kodebank' => 'BANK TRUCKING',  'namabank' => 'BANK TRUCKING',  'coa' => '01.02.02.01',  'tipe' => 'BANK',  'formatpenerimaan' => '87',  'formatpengeluaran' => '88',  'statusdefault' => '59',  'statusaktif' => '1',  'modifiedby' => 'ADMIN',  ]);
        Bank::create([  'kodebank' => 'BANK TRUCKING2',  'namabank' => 'BCA 8195147911',  'coa' => '01.02.02.03',  'tipe' => 'BANK',  'formatpenerimaan' => '266',  'formatpengeluaran' => '267',  'statusdefault' => '59',  'statusaktif' => '1',  'modifiedby' => 'ADMIN',  ]);
        Bank::create([  'kodebank' => 'BANK TRUCKING3',  'namabank' => 'BCA 8195157088',  'coa' => '01.02.02.05',  'tipe' => 'BANK',  'formatpenerimaan' => '268',  'formatpengeluaran' => '269',  'statusdefault' => '59',  'statusaktif' => '1',  'modifiedby' => 'ADMIN',  ]);
        Bank::create([  'kodebank' => 'PENGEMBALIAN KE PUSAT',  'namabank' => 'PENGEMBALIAN KE PUSAT BCA 8195157088',  'coa' => '01.02.02.05',  'tipe' => 'BANK',  'formatpenerimaan' => '',  'formatpengeluaran' => '277',  'statusdefault' => '59',  'statusaktif' => '1',  'modifiedby' => 'ADMIN',  ]);
    }
}
