<?php

namespace Database\Seeders;

use App\Models\HariLibur;
use Illuminate\Support\Facades\DB;


use Illuminate\Database\Seeder;

class HariLiburSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete harilibur");
        DB::statement("DBCC CHECKIDENT ('harilibur', RESEED, 1);");

        harilibur::create(['tgl' => '2022/1/1', 'Keterangan' => 'Tahun baru Masehi', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        harilibur::create(['tgl' => '2022/2/1', 'Keterangan' => 'Tahun Baru Imlek 2573 Kongzili', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        harilibur::create(['tgl' => '2022/2/28', 'Keterangan' => 'Isra Miraj Muhammad SAW', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        harilibur::create(['tgl' => '2022/3/3', 'Keterangan' => 'Hari Suci Nyepi 1944', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        harilibur::create(['tgl' => '2022/4/15', 'Keterangan' => 'Wafat Isa Al Masih', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        harilibur::create(['tgl' => '2022/5/2', 'Keterangan' => 'Idul Fitri 1443 Hijriah', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        harilibur::create(['tgl' => '2022/5/3', 'Keterangan' => 'Idul Fitri 1443 Hijriah', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        harilibur::create(['tgl' => '2022/5/16', 'Keterangan' => 'Hari Raya Waisak 2566 BE', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        harilibur::create(['tgl' => '2022/5/26', 'Keterangan' => 'Kenaikan Isa Al Masih', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        harilibur::create(['tgl' => '2022/6/1', 'Keterangan' => 'Hari Lahir Pancasila', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        harilibur::create(['tgl' => '2022/7/9', 'Keterangan' => 'Hari Raya Idul Adha 1443 Hijriah', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        harilibur::create(['tgl' => '2022/7/30', 'Keterangan' => 'Tahun Baru Islam 1444 Hijriah', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        harilibur::create(['tgl' => '2022/8/17', 'Keterangan' => 'Hari Kemerdekaan RI', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        harilibur::create(['tgl' => '2022/10/8', 'Keterangan' => 'Maulid Nabi Muhammad SAW', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        harilibur::create(['tgl' => '2022/12/25', 'Keterangan' => 'Hari Raya Natal', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
    }
}
