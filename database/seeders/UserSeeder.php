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

        user::create(['user' => 'ADMIN', 'name' => 'ADMIN', 'password' => '$2y$10$/Hmu7CfU2uKpe/1mm8eJ8uXm45NVcAMB9NBM34/vKnknRSSxIBPEa', 'cabang_id' => '1', 'karyawan_id' => '0', 'dashboard' => '', 'statusaktif' => '1', 'statusakses' => '356', 'email' => 'PT.TRANSPORINDO@GMAIL.COM', 'modifiedby' => '',]);
        user::create(['user' => 'YESSICA', 'name' => 'YESSICA', 'password' => '$2y$10$mJ.8JWppfDiVBOV4gx8lBebnmnAKEx0WSQ016vXyStbEPnsJB9yQO', 'cabang_id' => '1', 'karyawan_id' => '0', 'dashboard' => '', 'statusaktif' => '1', 'statusakses' => '356', 'email' => 'YESSICA_TAS@YAHOO.COM', 'modifiedby' => 'ADMIN',]);
        user::create(['user' => 'MERRY', 'name' => 'MERRY', 'password' => '$2y$10$AnkGpxY5M3tWz29eICM/0.89FDYN6kZyE1okNckUakEejC.jWpwce', 'cabang_id' => '1', 'karyawan_id' => '0', 'dashboard' => '', 'statusaktif' => '1', 'statusakses' => '356', 'email' => 'M3RRY_SU@YAHOO.COM', 'modifiedby' => 'YESSICA',]);
        user::create(['user' => 'AGNES', 'name' => 'AGNES', 'password' => '$2y$10$dRer7mf5S2lsi3eS3iVAZO39UGY2hplgdT2YB2NDKB4rfssRmv7Fm', 'cabang_id' => '1', 'karyawan_id' => '0', 'dashboard' => '', 'statusaktif' => '1', 'statusakses' => '356', 'email' => 'AGNES_TAS@YAHOO.COM', 'modifiedby' => 'YESSICA',]);
        user::create(['user' => 'JUNITA', 'name' => 'JUNITA', 'password' => '$2y$10$uzizVIADIQ/MAJVPbccNLOaKU9xmVEBTbYYcyf/rQFRH6.GwgHMjC', 'cabang_id' => '1', 'karyawan_id' => '0', 'dashboard' => '', 'statusaktif' => '1', 'statusakses' => '356', 'email' => 'JUNITATAS@GMAIL.COM', 'modifiedby' => 'YESSICA',]);
        user::create(['user' => 'ADELINE', 'name' => 'ADELINE', 'password' => '$2y$10$YxaHfaM2doJvQX2QWcoLaed/SROx0WhHKzqnIDdqLifE/Lozn9SlC', 'cabang_id' => '1', 'karyawan_id' => '0', 'dashboard' => '', 'statusaktif' => '1', 'statusakses' => '356', 'email' => 'ADELINEMARTHEUS.TAS@GMAIL.COM', 'modifiedby' => 'YESSICA',]);
        user::create(['user' => 'STEVEN', 'name' => 'STEVEN', 'password' => '$2y$10$BKnSnfFR31dtqWQABaiHQeZsonXkfiqrtWS2cyFs6CB8.m.a/YwBi', 'cabang_id' => '1', 'karyawan_id' => '0', 'dashboard' => '', 'statusaktif' => '1', 'statusakses' => '356', 'email' => 'STEVEN_TAS@YAHOO.COM', 'modifiedby' => 'YESSICA',]);

        // User::factory()->create();
    }
}
