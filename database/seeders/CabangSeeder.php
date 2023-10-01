<?php

namespace Database\Seeders;

use App\Models\Cabang;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CabangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete cabang");
        DB::statement("DBCC CHECKIDENT ('cabang', RESEED, 1);");

        cabang::create(['kodecabang' => 'PST', 'namacabang' => 'PUSAT', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '{"LOCATION":",","IPCLIENT":"180.241.47.10","IPSERVERLOCAL":"192.168.1.249","IPSERVERPUBLIC":"192.168.1.249","BROWSER":"CHROME","OS":"MOZILLA/5.0 (WINDOWS NT 10.0; WIN64; X64) APPLEWEBKIT/537.36 (KHTML, LIKE GECKO) CHROME/116.0.0.0 SAFARI/537.36"}',]);
        cabang::create(['kodecabang' => 'MDN', 'namacabang' => 'MEDAN', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '{"LOCATION":",","IPCLIENT":"36.71.141.71","IPSERVERLOCAL":"192.168.1.249","IPSERVERPUBLIC":"118.136.90.33","BROWSER":"CHROME","OS":"MOZILLA/5.0 (WINDOWS NT 10.0; WIN64; X64) APPLEWEBKIT/537.36 (KHTML, LIKE GECKO) CHROME/116.0.0.0 SAFARI/537.36"}',]);
        cabang::create(['kodecabang' => 'JKT', 'namacabang' => 'JAKARTA', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '{"LATLONG":",","IPCLIENT":"36.71.141.71","IPSERVER":"192.168.1.249","BROWSER":"CHROME","OS":"MOZILLA/5.0 (WINDOWS NT 10.0; WIN64; X64) APPLEWEBKIT/537.36 (KHTML, LIKE GECKO) CHROME/116.0.0.0 SAFARI/537.36"}',]);
        cabang::create(['kodecabang' => 'SBY', 'namacabang' => 'SURABAYA', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
        cabang::create(['kodecabang' => 'MKS', 'namacabang' => 'MAKASSAR', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '{"LOCATION":",","IPCLIENT":"180.241.47.10","IPSERVERLOCAL":"192.168.1.249","IPSERVERPUBLIC":"192.168.1.249","BROWSER":"CHROME","OS":"MOZILLA/5.0 (WINDOWS NT 10.0; WIN64; X64) APPLEWEBKIT/537.36 (KHTML, LIKE GECKO) CHROME/116.0.0.0 SAFARI/537.36"}',]);
        cabang::create(['kodecabang' => 'BTG', 'namacabang' => 'BITUNG', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
        cabang::create(['kodecabang' => 'TNL', 'namacabang' => 'JAKARTA TNL', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
    }
}
