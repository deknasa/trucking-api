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

        bank::create(['kodebank' => 'KAS TRUCKING', 'namabank' => 'KAS TRUCKING', 'coa' => '01.01.01.03', 'tipe' => 'KAS', 'statusdefault' => '58', 'statusaktif' => '1', 'formatpenerimaan' => '32', 'formatpengeluaran' => '33', 'modifiedby' => 'ADMIN', 'info' => '',]);
        bank::create(['kodebank' => 'BCA 0073726449', 'namabank' => 'BCA 0073726449', 'coa' => '01.02.03.04', 'tipe' => 'BANK', 'statusdefault' => '59', 'statusaktif' => '1', 'formatpenerimaan' => '268', 'formatpengeluaran' => '269', 'modifiedby' => 'ADMIN', 'info' => '',]);
        bank::create(['kodebank' => 'PENGEMBALIAN KE PUSAT BCA 0222515015', 'namabank' => 'PENGEMBALIAN KE PUSAT BCA 0222515015', 'coa' => '01.02.03.04', 'tipe' => 'BANK', 'statusdefault' => '59', 'statusaktif' => '1', 'formatpenerimaan' => '452', 'formatpengeluaran' => '277', 'modifiedby' => 'AGNES', 'info' => '{"LOCATION":",","IPCLIENT":"36.71.140.234","IPSERVERLOCAL":"192.168.1.249","IPSERVERPUBLIC":"192.168.1.249","BROWSER":"CHROME","OS":"MOZILLA/5.0 (WINDOWS NT 10.0; WIN64; X64) APPLEWEBKIT/537.36 (KHTML, LIKE GECKO) CHROME/109.0.0.0 SAFARI/537.36"}',]);
        bank::create(['kodebank' => 'BANK TRUCKING', 'namabank' => 'BANK TRUCKING', 'coa' => '01.02.03.01', 'tipe' => 'BANK', 'statusdefault' => '0', 'statusaktif' => '2', 'formatpenerimaan' => '87', 'formatpengeluaran' => '88', 'modifiedby' => 'ADMIN', 'info' => '',]);
        bank::create(['kodebank' => 'BCA 0073661088', 'namabank' => 'BCA 0073661088', 'coa' => '01.02.03.02', 'tipe' => 'BANK', 'statusdefault' => '0', 'statusaktif' => '2', 'formatpenerimaan' => '266', 'formatpengeluaran' => '267', 'modifiedby' => 'ADMIN', 'info' => '',]);
    }
}
