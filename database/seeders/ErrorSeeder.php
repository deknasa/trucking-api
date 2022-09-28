<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Error;
use Illuminate\Support\Facades\DB;


class ErrorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete [error]");
        DB::statement("DBCC CHECKIDENT ('[error]', RESEED, 1);");

        error::create([ 'kodeerror' => 'WI', 'keterangan' => 'WAJIB DI ISI', 'modifiedby' => 'ADMIN',]);
        error::create([ 'kodeerror' => 'SPI', 'keterangan' => 'SUDAH PERNAH INPUT', 'modifiedby' => 'ADMIN',]);
        error::create([ 'kodeerror' => 'SAP', 'keterangan' => 'SUDAH DI APPROVAL', 'modifiedby' => 'ADMIN',]);
        error::create([ 'kodeerror' => 'BADJ', 'keterangan' => 'BUKAN ENTRYAN JURNAL MANUAL', 'modifiedby' => 'ADMIN',]);

    }
}
