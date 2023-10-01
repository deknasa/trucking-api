<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MainTypeAkuntansi;
use Illuminate\Support\Facades\DB;

class MainTypeAkuntansiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete MainTypeAkuntansi");
        DB::statement("DBCC CHECKIDENT ('MainTypeAkuntansi', RESEED, 1);");

        maintypeakuntansi::create(['kodetype' => 'AKTIVA LANCAR', 'order' => '1110', 'keterangantype' => 'AKTIVA LANCAR', 'akuntansi_id' => '1', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
        maintypeakuntansi::create(['kodetype' => 'AKTIVA TETAP', 'order' => '1210', 'keterangantype' => 'AKTIVA TETAP', 'akuntansi_id' => '1', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
        maintypeakuntansi::create(['kodetype' => 'AKUN SEMENTARA', 'order' => '9110', 'keterangantype' => 'AKUN SEMENTARA', 'akuntansi_id' => '8', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
        maintypeakuntansi::create(['kodetype' => 'BEBAN', 'order' => '5111', 'keterangantype' => 'BEBAN', 'akuntansi_id' => '8', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
        maintypeakuntansi::create(['kodetype' => 'HUTANG JK. PANJANG', 'order' => '2210', 'keterangantype' => 'HUTANG JK. PANJANG', 'akuntansi_id' => '2', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
        maintypeakuntansi::create(['kodetype' => 'HUTANG LANCAR', 'order' => '2110', 'keterangantype' => 'HUTANG LANCAR', 'akuntansi_id' => '2', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
        maintypeakuntansi::create(['kodetype' => 'IKTISAR LABA RUGI', 'order' => '3115', 'keterangantype' => 'IKTISAR LABA RUGI', 'akuntansi_id' => '8', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
        maintypeakuntansi::create(['kodetype' => 'LABA/RUGI', 'order' => '3210', 'keterangantype' => 'LABA RUGI', 'akuntansi_id' => '8', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
        maintypeakuntansi::create(['kodetype' => 'MODAL', 'order' => '3310', 'keterangantype' => 'MODAL', 'akuntansi_id' => '3', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
        maintypeakuntansi::create(['kodetype' => 'PENDAPATAN', 'order' => '4110', 'keterangantype' => 'PENDAPATAN', 'akuntansi_id' => '8', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
    }
}
