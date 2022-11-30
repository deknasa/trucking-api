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
        error::create([ 'kodeerror' => 'USBA', 'keterangan' => 'UPAH SUPIR BELUM ADA', 'modifiedby' => 'ADMIN',]);
        error::create([ 'kodeerror' => 'NT', 'keterangan' => 'TIDAK ADA TRIP', 'modifiedby' => 'ADMIN',]);
        error::create([ 'kodeerror' => 'SPSD', 'keterangan' => 'SP SUDAH DIBENTUK', 'modifiedby' => 'ADMIN',]);
        error::create([ 'kodeerror' => 'RICX', 'keterangan' => 'RIC SUDAH DIBENTUK UNTUK EBS', 'modifiedby' => 'ADMIN',]);
        error::create([ 'kodeerror' => 'EBSX', 'keterangan' => 'EBS TIDAK BOLEH DIEDIT', 'modifiedby' => 'ADMIN',]);
        error::create([ 'kodeerror' => 'RICSD', 'keterangan' => 'RIC SUDAH DIBENTUK', 'modifiedby' => 'ADMIN',]);
        error::create([ 'kodeerror' => 'NRIC', 'keterangan' => 'TIDAK ADA RIC', 'modifiedby' => 'ADMIN',]);
        error::create([ 'kodeerror' => 'NB', 'keterangan' => 'NOMINAL BAYAR TIDAK BOLEH MELEBIHI NOMINAL PIUTANG', 'modifiedby' => 'ADMIN',]);

    }
}
