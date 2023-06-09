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

        maintypeakuntansi::create(['kodetype' => 'Aktiva Lancar', 'Order' => '1110', 'keterangantype' => 'Aktiva Lancar', 'akuntansi_id' => '1', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        maintypeakuntansi::create(['kodetype' => 'Aktiva Tetap', 'Order' => '1210', 'keterangantype' => 'Aktiva Tetap', 'akuntansi_id' => '1', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        maintypeakuntansi::create(['kodetype' => 'Akun Sementara', 'Order' => '9110', 'keterangantype' => 'Akun Sementara', 'akuntansi_id' => '8', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        maintypeakuntansi::create(['kodetype' => 'Beban', 'Order' => '5111', 'keterangantype' => 'Beban', 'akuntansi_id' => '8', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        maintypeakuntansi::create(['kodetype' => 'Hutang Jk. Panjang', 'Order' => '2210', 'keterangantype' => 'Hutang Jk. Panjang', 'akuntansi_id' => '2', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        maintypeakuntansi::create(['kodetype' => 'Hutang Lancar', 'Order' => '2110', 'keterangantype' => 'Hutang Lancar', 'akuntansi_id' => '2', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        maintypeakuntansi::create(['kodetype' => 'Iktisar Laba Rugi', 'Order' => '3115', 'keterangantype' => 'Iktisar Laba Rugi', 'akuntansi_id' => '8', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        maintypeakuntansi::create(['kodetype' => 'Laba/Rugi', 'Order' => '3210', 'keterangantype' => 'Laba Rugi', 'akuntansi_id' => '8', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        maintypeakuntansi::create(['kodetype' => 'Modal', 'Order' => '3310', 'keterangantype' => 'Modal', 'akuntansi_id' => '3', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        maintypeakuntansi::create(['kodetype' => 'Pendapatan', 'Order' => '4110', 'keterangantype' => 'Pendapatan', 'akuntansi_id' => '8', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
    }
}
