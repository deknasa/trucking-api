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

        Error::create(['kodeerror' => 'WI', 'keterangan' => 'WAJIB DI ISI', 'modifiedby' => 'ADMIN',]);
        Error::create(['kodeerror' => 'SPI', 'keterangan' => 'SUDAH PERNAH INPUT', 'modifiedby' => 'ADMIN',]);
        Error::create(['kodeerror' => 'SAP', 'keterangan' => 'SUDAH DI APPROVAL', 'modifiedby' => 'ADMIN',]);
        Error::create(['kodeerror' => 'BADJ', 'keterangan' => 'EDIT/DELETE TIDAK DIPERBOLEHKAN. KARENA DATA BUKAN BERASAL DARI JURNAL UMUM.', 'modifiedby' => 'ADMIN',]);
        Error::create(['kodeerror' => 'USBA', 'keterangan' => 'UPAH SUPIR BELUM ADA', 'modifiedby' => 'ADMIN',]);
        Error::create(['kodeerror' => 'NT', 'keterangan' => 'TIDAK ADA TRIP', 'modifiedby' => 'ADMIN',]);
        Error::create(['kodeerror' => 'SPSD', 'keterangan' => 'SURAT PENGANTAR SUDAH DIBENTUK', 'modifiedby' => 'ADMIN',]);
        Error::create(['kodeerror' => 'RICX', 'keterangan' => 'RIC TIDAK BISA DIEDIT', 'modifiedby' => 'ADMIN',]);
        Error::create(['kodeerror' => 'EBSX', 'keterangan' => 'EBS TIDAK BISA DIEDIT', 'modifiedby' => 'ADMIN',]);
        Error::create(['kodeerror' => 'RICSD', 'keterangan' => 'RIC UNTUK RENTANG WAKTU TERSEBUT SUDAH DIBENTUK', 'modifiedby' => 'ADMIN',]);
        Error::create(['kodeerror' => 'NRIC', 'keterangan' => 'TIDAK ADA RIC', 'modifiedby' => 'ADMIN',]);
        Error::create(['kodeerror' => 'NBP', 'keterangan' => 'NOMINAL BAYAR TIDAK BOLEH MELEBIHI NOMINAL PIUTANG', 'modifiedby' => 'ADMIN',]);
        Error::create(['kodeerror' => 'NBH', 'keterangan' => 'NOMINAL BAYAR TIDAK BOLEH MELEBIHI NOMINAL HUTANG', 'modifiedby' => 'ADMIN',]);
        Error::create(['kodeerror' => 'NSP', 'keterangan' => 'TIDAK ADA SURAT PENGANTAR', 'modifiedby' => 'ADMIN',]);
        Error::create(['kodeerror' => 'SDC', 'keterangan' => 'SUDAH CETAK', 'modifiedby' => 'ADMIN',]);
        Error::create(['kodeerror' => 'WP', 'keterangan' => 'WAJIB DI PILIH', 'modifiedby' => 'ADMIN',]);
        Error::create(['kodeerror' => 'SMIN', 'keterangan' => 'NILAI STOK MINUS', 'modifiedby' => 'ADMIN',]);
        Error::create(['kodeerror' => 'WISP', 'keterangan' => 'WAJIB ISI SUPPLIER ATAU PELANGGAN', 'modifiedby' => 'ADMIN',]);
        Error::create(['kodeerror' => 'PSP', 'keterangan' => 'HANYA BOLEH PILIH SALAH SATU, PELANGGAN ATAU SUPPLIER', 'modifiedby' => 'ADMIN',]);
        Error::create(['kodeerror' => 'EMAIL', 'keterangan' => 'HARUS ALAMAT E-MAIL YANG VALID', 'modifiedby' => 'ADMIN',]);
        Error::create(['kodeerror' => 'GT-ANGKA-0', 'keterangan' => 'NILAI HARUS LEBIH BESAR DARI 0', 'modifiedby' => 'ADMIN',]);
        Error::create(['kodeerror' => 'MIN', 'keterangan' => 'HARUS DIBAWAH', 'modifiedby' => 'ADMIN',]);
        Error::create(['kodeerror' => 'BTSANGKA', 'keterangan' => 'ANGKA', 'modifiedby' => 'ADMIN',]);
        Error::create(['kodeerror' => 'BATASNILAI', 'keterangan' => 'HARUS', 'modifiedby' => 'ADMIN',]);
        Error::create(['kodeerror' => 'MAX', 'keterangan' => 'HARUS DIATAS', 'modifiedby' => 'ADMIN',]);
        Error::create(['kodeerror' => 'TSF', 'keterangan' => 'ISIAN TIDAK SESUAI FORMAT', 'modifiedby' => 'ADMIN',]);
    }
}
