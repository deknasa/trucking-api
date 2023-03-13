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


        harilibur::create(['tgl' => '2023/1/1', 'keterangan' => 'TAHUN BARU', 'statusaktif' => '1', 'modifiedby' => 'ryan',]);
        harilibur::create(['tgl' => '2023/1/22', 'keterangan' => 'TAHUN BARU IMLEK', 'statusaktif' => '1', 'modifiedby' => 'ryan',]);
        harilibur::create(['tgl' => '2023/2/18', 'keterangan' => 'ISRA MIRAJ', 'statusaktif' => '1', 'modifiedby' => 'ryan',]);
        harilibur::create(['tgl' => '2023/3/22', 'keterangan' => 'NYEPI', 'statusaktif' => '1', 'modifiedby' => 'ryan',]);
        harilibur::create(['tgl' => '2023/4/7', 'keterangan' => 'WAFAT ISA ALMASIH', 'statusaktif' => '1', 'modifiedby' => 'ryan',]);
        harilibur::create(['tgl' => '2023/4/22', 'keterangan' => 'HARI RAYA IDUL FITRI', 'statusaktif' => '1', 'modifiedby' => 'ryan',]);
        harilibur::create(['tgl' => '2023/4/23', 'keterangan' => 'HARI RAYA IDUL FITRI', 'statusaktif' => '1', 'modifiedby' => 'ryan',]);
        harilibur::create(['tgl' => '2023/5/1', 'keterangan' => 'HARI BURUH', 'statusaktif' => '1', 'modifiedby' => 'ryan',]);
        harilibur::create(['tgl' => '2023/5/18', 'keterangan' => 'KENAIKAN ISA ALMASIH', 'statusaktif' => '1', 'modifiedby' => 'ryan',]);
        harilibur::create(['tgl' => '2023/6/1', 'keterangan' => 'HARI LAHIR PANCASILA', 'statusaktif' => '1', 'modifiedby' => 'ryan',]);
        harilibur::create(['tgl' => '2023/6/4', 'keterangan' => 'HARI RAYA WAISAK', 'statusaktif' => '1', 'modifiedby' => 'ryan',]);
        harilibur::create(['tgl' => '2023/6/29', 'keterangan' => 'IDUL ADHA', 'statusaktif' => '1', 'modifiedby' => 'ryan',]);
        harilibur::create(['tgl' => '2023/7/19', 'keterangan' => 'TAHUN BARU ISLAM', 'statusaktif' => '1', 'modifiedby' => 'ryan',]);
        harilibur::create(['tgl' => '2023/8/17', 'keterangan' => 'HARI KEMERDEKAAN', 'statusaktif' => '1', 'modifiedby' => 'ryan',]);
        harilibur::create(['tgl' => '2023/9/28', 'keterangan' => 'MAULID NABI MUHAMMAD', 'statusaktif' => '1', 'modifiedby' => 'ryan',]);
        harilibur::create(['tgl' => '2023/12/25', 'keterangan' => 'NATAL', 'statusaktif' => '1', 'modifiedby' => 'ryan',]);
    }
}
