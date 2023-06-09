<?php

namespace Database\Seeders;
use App\Models\Akuntansi;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Seeder;

class AkuntansiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete Akuntansi");
        DB::statement("DBCC CHECKIDENT ('Akuntansi', RESEED, 1);");

        Akuntansi::create(['kodeakuntansi' => 'AKTIVA', 'keterangan' => 'AKTIVA', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        Akuntansi::create(['kodeakuntansi' => 'HUTANG', 'keterangan' => 'HUTANG', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        Akuntansi::create(['kodeakuntansi' => 'MODAL', 'keterangan' => 'MODAL', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        Akuntansi::create(['kodeakuntansi' => 'BEBAN', 'keterangan' => 'BEBAN', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);

    }
}
