<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {


        DB::statement("delete Role");
        DB::statement("DBCC CHECKIDENT ('Role', RESEED, 1);");

        role::create([ 'rolename' => 'ADMIN', 'modifiedby' => 'ADMIN', 'info' => '',]);
        role::create([ 'rolename' => 'TEST', 'modifiedby' => 'AGNES', 'info' => '{"LOCATION":",","IPCLIENT":"36.71.143.16","IPSERVERLOCAL":"192.168.1.249","IPSERVERPUBLIC":"36.94.216.205","BROWSER":"CHROME","OS":"MOZILLA/5.0 (WINDOWS NT 10.0; WIN64; X64) APPLEWEBKIT/537.36 (KHTML, LIKE GECKO) CHROME/109.0.0.0 SAFARI/537.36"}',]);
        role::create([ 'rolename' => 'MANDOR', 'modifiedby' => 'AGNES', 'info' => '{"LOCATION":",","IPCLIENT":"36.71.143.16","IPSERVERLOCAL":"192.168.1.249","IPSERVERPUBLIC":"36.94.216.205","BROWSER":"CHROME","OS":"MOZILLA/5.0 (WINDOWS NT 10.0; WIN64; X64) APPLEWEBKIT/537.36 (KHTML, LIKE GECKO) CHROME/109.0.0.0 SAFARI/537.36"}',]);
        role::create([ 'rolename' => 'KASIR', 'modifiedby' => 'ADMIN', 'info' => '{"LOCATION":",","IPCLIENT":"36.71.143.16","IPSERVERLOCAL":"192.168.1.249","IPSERVERPUBLIC":"118.136.18.74","BROWSER":"CHROME","OS":"MOZILLA/5.0 (WINDOWS NT 10.0; WIN64; X64) APPLEWEBKIT/537.36 (KHTML, LIKE GECKO) CHROME/109.0.0.0 SAFARI/537.36"}',]);
        role::create([ 'rolename' => 'TEST1', 'modifiedby' => 'AGNES', 'info' => '{"LOCATION":",","IPCLIENT":"36.71.143.16","IPSERVERLOCAL":"192.168.1.249","IPSERVERPUBLIC":"36.94.216.205","BROWSER":"CHROME","OS":"MOZILLA/5.0 (WINDOWS NT 10.0; WIN64; X64) APPLEWEBKIT/537.36 (KHTML, LIKE GECKO) CHROME/109.0.0.0 SAFARI/537.36"}',]);
        role::create([ 'rolename' => 'ADMIN SPAREPART', 'modifiedby' => 'ADMIN', 'info' => '{"LOCATION":",","IPCLIENT":"36.71.143.16","IPSERVERLOCAL":"192.168.1.249","IPSERVERPUBLIC":"118.136.18.74","BROWSER":"CHROME","OS":"MOZILLA/5.0 (WINDOWS NT 10.0; WIN64; X64) APPLEWEBKIT/537.36 (KHTML, LIKE GECKO) CHROME/109.0.0.0 SAFARI/537.36"}',]);
        role::create([ 'rolename' => 'KEPALA KEUANGAN', 'modifiedby' => 'ADMIN', 'info' => '{"LOCATION":",","IPCLIENT":"36.71.143.16","IPSERVERLOCAL":"192.168.1.249","IPSERVERPUBLIC":"118.136.18.74","BROWSER":"CHROME","OS":"MOZILLA/5.0 (WINDOWS NT 10.0; WIN64; X64) APPLEWEBKIT/537.36 (KHTML, LIKE GECKO) CHROME/109.0.0.0 SAFARI/537.36"}',]);
        role::create([ 'rolename' => 'ADMIN TRIP', 'modifiedby' => 'ADMIN', 'info' => '{"LOCATION":",","IPCLIENT":"36.71.137.12","IPSERVERLOCAL":"192.168.1.249","IPSERVERPUBLIC":"118.136.18.74","BROWSER":"CHROME","OS":"MOZILLA/5.0 (WINDOWS NT 10.0; WIN64; X64) APPLEWEBKIT/537.36 (KHTML, LIKE GECKO) CHROME/109.0.0.0 SAFARI/537.36"}',]);
        role::create([ 'rolename' => 'KACAB', 'modifiedby' => 'ADMIN', 'info' => '{"LOCATION":",","IPCLIENT":"36.71.137.12","IPSERVERLOCAL":"192.168.1.249","IPSERVERPUBLIC":"118.136.18.74","BROWSER":"CHROME","OS":"MOZILLA/5.0 (WINDOWS NT 10.0; WIN64; X64) APPLEWEBKIT/537.36 (KHTML, LIKE GECKO) CHROME/109.0.0.0 SAFARI/537.36"}',]);    }
}
