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

        Error::create([ 'kodeerror' => 'WI', 'keterangan' => 'WAJIB DI ISI', 'modifiedby' => 'ADMIN',]);
        Error::create([ 'kodeerror' => 'SPI', 'keterangan' => 'SUDAH PERNAH INPUT', 'modifiedby' => 'ADMIN',]);
        Error::create([ 'kodeerror' => 'SAP', 'keterangan' => 'SUDAH DI APPROVAL', 'modifiedby' => 'ADMIN',]);
        Error::create([ 'kodeerror' => 'BADJ', 'keterangan' => 'EDIT/DELETE TIDAK DIPERBOLEHKAN. KARENA DATA BUKAN BERASAL DARI JURNAL UMUM.', 'modifiedby' => 'ADMIN',]);
        Error::create([ 'kodeerror' => 'USBA', 'keterangan' => 'UPAH SUPIR BELUM ADA', 'modifiedby' => 'ADMIN',]);
        Error::create([ 'kodeerror' => 'NT', 'keterangan' => 'TIDAK ADA TRIP', 'modifiedby' => 'ADMIN',]);
        Error::create([ 'kodeerror' => 'SPSD', 'keterangan' => 'SURAT PENGANTAR SUDAH DIBENTUK', 'modifiedby' => 'ADMIN',]);
        Error::create([ 'kodeerror' => 'RICX', 'keterangan' => 'RIC TIDAK BISA DIEDIT', 'modifiedby' => 'ADMIN',]);
        Error::create([ 'kodeerror' => 'EBSX', 'keterangan' => 'EBS TIDAK BISA DIEDIT', 'modifiedby' => 'ADMIN',]);
        Error::create([ 'kodeerror' => 'RICSD', 'keterangan' => 'RIC UNTUK RENTANG WAKTU TERSEBUT SUDAH DIBENTUK', 'modifiedby' => 'ADMIN',]);
        Error::create([ 'kodeerror' => 'NRIC', 'keterangan' => 'TIDAK ADA RIC', 'modifiedby' => 'ADMIN',]);
        Error::create([ 'kodeerror' => 'NBP', 'keterangan' => 'NOMINAL BAYAR TIDAK BOLEH MELEBIHI NOMINAL PIUTANG', 'modifiedby' => 'ADMIN',]);
        Error::create([ 'kodeerror' => 'NBH', 'keterangan' => 'NOMINAL BAYAR TIDAK BOLEH MELEBIHI NOMINAL HUTANG', 'modifiedby' => 'ADMIN',]);
        Error::create([ 'kodeerror' => 'NSP', 'keterangan' => 'TIDAK ADA SURAT PENGANTAR', 'modifiedby' => 'ADMIN',]);
        Error::create([ 'kodeerror' => 'SDC', 'keterangan' => 'SUDAH CETAK', 'modifiedby' => 'ADMIN',]);
        Error::create([ 'kodeerror' => 'WP', 'keterangan' => 'WAJIB DI PILIH', 'modifiedby' => 'ADMIN',]);
        Error::create([ 'kodeerror' => 'SMIN', 'keterangan' => 'NILAI STOK MINUS', 'modifiedby' => 'ADMIN',]);
        Error::create([ 'kodeerror' => 'WISP', 'keterangan' => 'WAJIB ISI SUPPLIER ATAU PELANGGAN', 'modifiedby' => 'ADMIN',]);
        Error::create([ 'kodeerror' => 'PSP', 'keterangan' => 'HANYA BOLEH PILIH SALAH SATU, PELANGGAN ATAU SUPPLIER', 'modifiedby' => 'ADMIN',]);
        Error::create([ 'kodeerror' => 'EMAIL', 'keterangan' => 'HARUS ALAMAT E-MAIL YANG VALID', 'modifiedby' => 'ADMIN',]);
        Error::create([ 'kodeerror' => 'GT-ANGKA-0', 'keterangan' => 'NILAI HARUS LEBIH BESAR DARI 0', 'modifiedby' => 'ADMIN',]);
        Error::create([ 'kodeerror' => 'MIN', 'keterangan' => 'HARUS DIBAWAH', 'modifiedby' => 'ADMIN',]);
        Error::create([ 'kodeerror' => 'BTSANGKA', 'keterangan' => 'ANGKA', 'modifiedby' => 'ADMIN',]);
        Error::create([ 'kodeerror' => 'BATASNILAI', 'keterangan' => 'HARUS', 'modifiedby' => 'ADMIN',]);
        Error::create([ 'kodeerror' => 'MAX', 'keterangan' => 'HARUS DIATAS', 'modifiedby' => 'ADMIN',]);
        Error::create([ 'kodeerror' => 'TSF', 'keterangan' => 'ISIAN TIDAK SESUAI FORMAT', 'modifiedby' => 'ADMIN',]);
        Error::create([ 'kodeerror' => 'NBPT', 'keterangan' => 'NOMINAL BAYAR TIDAK BOLEH MELEBIHI NOMINAL PINJAMAN', 'modifiedby' => 'ADMIN',]);
        Error::create([ 'kodeerror' => 'NTC', 'keterangan' => 'NILAI TIDAK COCOK', 'modifiedby' => 'ADMIN',]);
        Error::create([ 'kodeerror' => 'FXLS', 'keterangan' => 'HARUS BERTIPE XLS ATAU XLSX', 'modifiedby' => 'ADMIN',]);
        Error::create([ 'kodeerror' => 'SATL', 'keterangan' => 'PROSES TIDAK BISA LANJUT KARENA SUDAH DIPAKAI DI TRANSAKSI LAIN', 'modifiedby' => 'ADMIN',]);
        Error::create([ 'kodeerror' => 'STM', 'keterangan' => 'SISA TIDAK BOLEH MINUS', 'modifiedby' => 'ADMIN',]);
        Error::create([ 'kodeerror' => 'TDT', 'keterangan' => 'TRANSAKSI BERASAL DARI INPUTAN TRANSAKSI LAIN', 'modifiedby' => 'ADMIN',]);
        Error::create([ 'kodeerror' => 'ETS', 'keterangan' => 'HANYA BISA EDIT/DELETE DI TANGGAL YANG SAMA', 'modifiedby' => 'ADMIN',]);
        Error::create([ 'kodeerror' => 'TBT', 'keterangan' => 'TIDAK BISA MEMILIH TANGGAL TERSEBUT', 'modifiedby' => 'ADMIN',]);
        Error::create([ 'kodeerror' => 'TSTB', 'keterangan' => 'TANGGAL SUDAH TIDAK BERLAKU', 'modifiedby' => 'ADMIN',]);
        Error::create([ 'kodeerror' => 'TSPTB', 'keterangan' => 'TANGGAL TIDAK BISA DI PROSES  SEBELUM TANGGAL TUTUP BUKU', 'modifiedby' => 'ADMIN',]);
        Error::create([ 'kodeerror' => 'SPIT', 'keterangan' => 'SUDAH PERNAH DI INPUT DI TRADO', 'modifiedby' => 'ADMIN',]);
        Error::create([ 'kodeerror' => 'HF', 'keterangan' => 'FORMAT JAM SALAH', 'modifiedby' => 'ADMIN',]);
        Error::create([ 'kodeerror' => 'TAB', 'keterangan' => 'TIDAK ADA ABSENSI. SILAHKAN INPUT ABSENSI', 'modifiedby' => 'ADMIN',]);
        Error::create([ 'kodeerror' => 'DF', 'keterangan' => 'FORMAT TANGGAL SALAH', 'modifiedby' => 'ADMIN',]);
        Error::create([ 'kodeerror' => 'ENTER', 'keterangan' => 'TEKAN ENTER PADA CELL UNTUK MENYIMPAN PERUBAHAN', 'modifiedby' => 'ADMIN',]);
        Error::create([ 'kodeerror' => 'WG', 'keterangan' => 'FILE HARUS BERUPA GAMBAR', 'modifiedby' => 'ADMIN',]);
        Error::create([ 'kodeerror' => 'TBMINUS', 'keterangan' => 'NILAI TIDAK BOLEH MINUS', 'modifiedby' => 'ADMIN',]);
        Error::create([ 'kodeerror' => 'NTM', 'keterangan' => 'NILAI TIDAK BOLEH MINUS', 'modifiedby' => 'ADMIN',]);
    }
}
