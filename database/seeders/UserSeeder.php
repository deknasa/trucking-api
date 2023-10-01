<?php

namespace Database\Seeders;

use App\Models\Cabang;
use App\Models\Parameter;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // User::create([
        //     'user' => 'ADMIN',
        //     'name' => 'ADMIN',
        //     'cabang_id' => Cabang::where('kodecabang', 'PST')->first()->id,
        //     'statusaktif' => Parameter::where('grp', 'STATUS AKTIF')->where('text', 'AKTIF')->first()->id,
        //     'statusakses' => Parameter::where('grp', 'STATUS AKSES')->where('text', 'PUBLIC')->first()->id,
        //     'email' =>'pt.transporindo@gmail.com',
        //     'password' => bcrypt('123456'),
        // ]);

        DB::statement("delete [user]");
        DB::statement("DBCC CHECKIDENT ('[user]', RESEED, 1);");

        user::create(['user' => 'ADMIN', 'name' => 'ADMIN', 'password' => '$2y$10$H8orVoif9sL1QtAf2ay4i.1biY21TfOkp0rm629dAl26AgTEzZPmO', 'cabang_id' => '1', 'karyawan_id' => '0', 'dashboard' => '', 'statusaktif' => '1', 'statusakses' => '356', 'email' => 'PT.TRANSPORINDO@GMAIL.COM', 'modifiedby' => '', 'info' => '',]);
        user::create(['user' => 'YESSICA', 'name' => 'YESSICA', 'password' => '$2y$10$mJ.8JWppfDiVBOV4gx8lBebnmnAKEx0WSQ016vXyStbEPnsJB9yQO', 'cabang_id' => '1', 'karyawan_id' => '0', 'dashboard' => '', 'statusaktif' => '1', 'statusakses' => '356', 'email' => 'YESSICA_TAS@YAHOO.COM', 'modifiedby' => 'ADMIN', 'info' => '',]);
        user::create(['user' => 'MERRY', 'name' => 'MERRY', 'password' => '$2y$10$sMOBF6RWuVbTWkNVRBsJCupoEXsPlXtbtO8BFbAnibcvH8NF7JVHy', 'cabang_id' => '1', 'karyawan_id' => '0', 'dashboard' => '', 'statusaktif' => '1', 'statusakses' => '356', 'email' => 'M3RRY_SU@YAHOO.COM', 'modifiedby' => 'MERRY', 'info' => '{"LOCATION":",","IPCLIENT":"36.71.137.12","IPSERVERLOCAL":"192.168.1.249","IPSERVERPUBLIC":"118.136.18.74","BROWSER":"CHROME","OS":"MOZILLA/5.0 (WINDOWS NT 10.0; WIN64; X64) APPLEWEBKIT/537.36 (KHTML, LIKE GECKO) CHROME/109.0.0.0 SAFARI/537.36"}',]);
        user::create(['user' => 'AGNES', 'name' => 'AGNES', 'password' => '$2y$10$Y6NJSm0RLBw/dzrl1H/Or.qk6aOW5VrMTrvrqcMMKQf.LArWGyGPW', 'cabang_id' => '1', 'karyawan_id' => '0', 'dashboard' => '', 'statusaktif' => '1', 'statusakses' => '356', 'email' => 'AGNES_TAS@YAHOO.COM', 'modifiedby' => 'AGNES', 'info' => '{"LOCATION":",","IPCLIENT":"36.71.137.12","IPSERVERLOCAL":"192.168.1.249","IPSERVERPUBLIC":"118.136.18.74","BROWSER":"CHROME","OS":"MOZILLA/5.0 (WINDOWS NT 10.0; WIN64; X64) APPLEWEBKIT/537.36 (KHTML, LIKE GECKO) CHROME/109.0.0.0 SAFARI/537.36"}',]);
        user::create(['user' => 'JUNITA', 'name' => 'JUNITA', 'password' => '$2y$10$uzizVIADIQ/MAJVPbccNLOaKU9xmVEBTbYYcyf/rQFRH6.GwgHMjC', 'cabang_id' => '1', 'karyawan_id' => '0', 'dashboard' => '', 'statusaktif' => '1', 'statusakses' => '356', 'email' => 'JUNITATAS@GMAIL.COM', 'modifiedby' => 'YESSICA', 'info' => '',]);
        user::create(['user' => 'ADELINE', 'name' => 'ADELINE', 'password' => '$2y$10$YxaHfaM2doJvQX2QWcoLaed/SROx0WhHKzqnIDdqLifE/Lozn9SlC', 'cabang_id' => '1', 'karyawan_id' => '0', 'dashboard' => '', 'statusaktif' => '1', 'statusakses' => '356', 'email' => 'ADELINEMARTHEUS.TAS@GMAIL.COM', 'modifiedby' => 'YESSICA', 'info' => '',]);
        user::create(['user' => 'STEVEN', 'name' => 'STEVEN', 'password' => '$2y$10$BKnSnfFR31dtqWQABaiHQeZsonXkfiqrtWS2cyFs6CB8.m.a/YwBi', 'cabang_id' => '1', 'karyawan_id' => '0', 'dashboard' => '', 'statusaktif' => '1', 'statusakses' => '356', 'email' => 'STEVEN_TAS@YAHOO.COM', 'modifiedby' => 'YESSICA', 'info' => '',]);
        user::create(['user' => 'LIVIN', 'name' => 'LIVIN', 'password' => '$2y$10$MYYt2Gf/IyFG77L/Z8pUcOofHMoQ7rOcIRaOqoxifKJyif2aHZX0O', 'cabang_id' => '3', 'karyawan_id' => '0', 'dashboard' => '', 'statusaktif' => '1', 'statusakses' => '356', 'email' => 'LIVINSENDATRANSPORINDO@GMAIL.COM', 'modifiedby' => 'ADMIN', 'info' => '',]);
        user::create(['user' => 'LIA', 'name' => 'LIA', 'password' => '$2y$10$a84AlWlpoTsldehYs.sHDOaw2kQ4iydfadkbGlhnYufSED4NIHM82', 'cabang_id' => '3', 'karyawan_id' => '0', 'dashboard' => '', 'statusaktif' => '1', 'statusakses' => '356', 'email' => 'YULIANI.YUMIE@YAHOO.COM', 'modifiedby' => 'LIA', 'info' => '{"LOCATION":",","IPCLIENT":"36.94.216.205","IPSERVERLOCAL":"192.168.1.249","IPSERVERPUBLIC":"118.136.18.74","BROWSER":"CHROME","OS":"MOZILLA/5.0 (WINDOWS NT 10.0; WIN64; X64) APPLEWEBKIT/537.36 (KHTML, LIKE GECKO) CHROME/117.0.0.0 SAFARI/537.36"}',]);
        user::create(['user' => 'YOHANES', 'name' => 'YOHANES', 'password' => '$2y$10$CNLio.jxBZVDiIpp6pBVpu/ARoQnfg2FZU3xXUAGOySN1xzI9EJj6', 'cabang_id' => '3', 'karyawan_id' => '0', 'dashboard' => '', 'statusaktif' => '1', 'statusakses' => '356', 'email' => 'DENOSFLOREST@YAHOO.CO.ID', 'modifiedby' => 'ADMIN', 'info' => '',]);
        user::create(['user' => 'HARIYANTO', 'name' => 'HARIYANTO', 'password' => '$2y$10$JMjRew3uq4p0M8ApshhPOuzsHjiEVepfRj/804lOUI8mTPjjDrRJ6', 'cabang_id' => '3', 'karyawan_id' => '0', 'dashboard' => '', 'statusaktif' => '1', 'statusakses' => '356', 'email' => 'HARIYANTO_TAS@YAHOO.COM', 'modifiedby' => 'ADMIN', 'info' => '{"LOCATION":",","IPCLIENT":"36.71.137.12","IPSERVERLOCAL":"192.168.1.249","IPSERVERPUBLIC":"118.136.18.74","BROWSER":"CHROME","OS":"MOZILLA/5.0 (WINDOWS NT 10.0; WIN64; X64) APPLEWEBKIT/537.36 (KHTML, LIKE GECKO) CHROME/109.0.0.0 SAFARI/537.36"}',]);
        user::create(['user' => 'NUR', 'name' => 'NUR', 'password' => '$2y$10$otoj9HVM.oRGzEXwmEFHNeZ8cSQmHhu/vrTXUB/tDZyYKsPa95msu', 'cabang_id' => '3', 'karyawan_id' => '0', 'dashboard' => '', 'statusaktif' => '1', 'statusakses' => '356', 'email' => 'FAIQOH09_TAS@YAHOO.COM', 'modifiedby' => 'NUR', 'info' => '{"LOCATION":",","IPCLIENT":"36.66.126.141","IPSERVERLOCAL":"192.168.1.249","IPSERVERPUBLIC":"118.136.18.74","BROWSER":"CHROME","OS":"MOZILLA/5.0 (WINDOWS NT 10.0; WIN64; X64) APPLEWEBKIT/537.36 (KHTML, LIKE GECKO) CHROME/109.0.0.0 SAFARI/537.36"}',]);
        user::create(['user' => 'IHSAN', 'name' => 'IHSAN', 'password' => '$2y$10$HGB/uXgvHYolxCTVEi4ezedUbCm7STDsCS2TfxJRCvqnkQ.GiCa7O', 'cabang_id' => '3', 'karyawan_id' => '0', 'dashboard' => '', 'statusaktif' => '1', 'statusakses' => '356', 'email' => 'IHSAN_TASJKT@YAHOO.COM', 'modifiedby' => 'IHSAN', 'info' => '{"LOCATION":",","IPCLIENT":"36.66.126.141","IPSERVERLOCAL":"192.168.1.249","IPSERVERPUBLIC":"36.94.216.205","BROWSER":"CHROME","OS":"MOZILLA/5.0 (WINDOWS NT 10.0; WIN64; X64) APPLEWEBKIT/537.36 (KHTML, LIKE GECKO) CHROME/109.0.0.0 SAFARI/537.36"}',]);
        user::create(['user' => 'FIRDHA', 'name' => 'FIRDHA', 'password' => '$2y$10$v.ZFIV8uWIyvthLqWtftN.d0uvvs.bXGiss4h0yyQLBEYXoTvK7uG', 'cabang_id' => '3', 'karyawan_id' => '0', 'dashboard' => '', 'statusaktif' => '1', 'statusakses' => '356', 'email' => 'RISZHAFIRDHATRANSPORINDOJAKARTA@YAHOO.CO.ID', 'modifiedby' => 'FIRDHA', 'info' => '{"LOCATION":",","IPCLIENT":"36.66.126.141","IPSERVERLOCAL":"192.168.1.249","IPSERVERPUBLIC":"118.136.18.74","BROWSER":"CHROME","OS":"MOZILLA/5.0 (WINDOWS NT 10.0; WIN64; X64) APPLEWEBKIT/537.36 (KHTML, LIKE GECKO) CHROME/109.0.0.0 SAFARI/537.36"}',]);
        user::create(['user' => 'AINI', 'name' => 'AINI', 'password' => '$2y$10$80yXkFYooLZ5MuinUSo.Ees.X.8x8aYXo/4Z4BUTdSn.rm8FTZUPa', 'cabang_id' => '3', 'karyawan_id' => '0', 'dashboard' => '', 'statusaktif' => '1', 'statusakses' => '356', 'email' => 'AINI.TRANSPORINDO@GMAIL.COM', 'modifiedby' => 'ADMIN', 'info' => '',]);
        user::create(['user' => 'WAWA', 'name' => 'WAWA', 'password' => '$2y$10$WLghzcrl.sJFNQvl6Ec7FubaDxRNDuYVrrbDyH5MC7YbMildjCxkG', 'cabang_id' => '3', 'karyawan_id' => '0', 'dashboard' => '', 'statusaktif' => '1', 'statusakses' => '356', 'email' => 'WAWA_TAS@YAHOO.COM', 'modifiedby' => 'ADMIN', 'info' => '',]);
        user::create(['user' => 'ROBERT', 'name' => 'ROBERT', 'password' => '$2y$10$LbUuZKl5fGAaKSap2JC1duDEoCttXbYhe1WUJ/hjCTUU7/MKcXgK6', 'cabang_id' => '3', 'karyawan_id' => '0', 'dashboard' => '', 'statusaktif' => '1', 'statusakses' => '356', 'email' => 'BERT21179@YAHOO.COM', 'modifiedby' => 'ADMIN', 'info' => '',]);
        user::create(['user' => 'RYAN', 'name' => 'RYAN', 'password' => '$2y$10$HtkPB/mDcvO5Y8CkiJCegeXVyLWLFlhE95KSAn70RFxHErcJg7Y5a', 'cabang_id' => '1', 'karyawan_id' => '0', 'dashboard' => '', 'statusaktif' => '1', 'statusakses' => '356', 'email' => 'RYAN_VIXY1402@YAHOO.COM', 'modifiedby' => 'ADMIN', 'info' => '',]);
        user::create(['user' => 'BUNGA IT', 'name' => 'BUNGA IT', 'password' => '$2y$10$7Ex3oZiv5i2dbTyJcgpM7Omctt0VVDHWrJ/wBiVN88vgvcpNs1N2e', 'cabang_id' => '3', 'karyawan_id' => '0', 'dashboard' => '', 'statusaktif' => '1', 'statusakses' => '357', 'email' => 'BUNGATASPUSAT@GMAIL.COM', 'modifiedby' => 'ADMIN', 'info' => '{"LOCATION":",","IPCLIENT":"36.71.137.12","IPSERVERLOCAL":"192.168.1.249","IPSERVERPUBLIC":"118.136.18.74","BROWSER":"CHROME","OS":"MOZILLA/5.0 (WINDOWS NT 10.0; WIN64; X64) APPLEWEBKIT/537.36 (KHTML, LIKE GECKO) CHROME/116.0.0.0 SAFARI/537.36"}',]);
        user::create(['user' => 'TEST', 'name' => 'TEST', 'password' => '$2y$10$pLtsuaVq8gH22Le3C/I15O35hhLbDALh2H1KVTgsgP7XLTihOj2zS', 'cabang_id' => '3', 'karyawan_id' => '0', 'dashboard' => '', 'statusaktif' => '1', 'statusakses' => '356', 'email' => 'RYAN.VIXY1402@GMAIL.COM', 'modifiedby' => 'RYAN', 'info' => '',]);
        // User::factory()->create();
    }
}
