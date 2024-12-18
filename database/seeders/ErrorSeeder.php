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

        error::create(['kodeerror' => 'WI', 'keterangan' => 'WAJIB DI ISI', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'SPI', 'keterangan' => 'SUDAH PERNAH INPUT', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'SAP', 'keterangan' => 'SUDAH DI APPROVAL', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'BADJ', 'keterangan' => 'EDIT/DELETE TIDAK DIPERBOLEHKAN. KARENA DATA BUKAN BERASAL DARI JURNAL UMUM.', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'USBA', 'keterangan' => 'UPAH SUPIR BELUM ADA', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'NT', 'keterangan' => 'TIDAK ADA TRIP', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'SPSD', 'keterangan' => 'SURAT PENGANTAR SUDAH DIBENTUK', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'RICX', 'keterangan' => 'RIC TIDAK BISA DIEDIT', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'EBSX', 'keterangan' => 'EBS TIDAK BISA DIEDIT', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'RICSD', 'keterangan' => 'RIC UNTUK RENTANG WAKTU TERSEBUT SUDAH DIBENTUK', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'NRIC', 'keterangan' => 'TIDAK ADA RIC', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'NBP', 'keterangan' => 'NOMINAL BAYAR TIDAK BOLEH MELEBIHI NOMINAL PIUTANG', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'NBH', 'keterangan' => 'NOMINAL BAYAR TIDAK BOLEH MELEBIHI NOMINAL HUTANG', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'NSP', 'keterangan' => 'TIDAK ADA SURAT PENGANTAR', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'SDC', 'keterangan' => 'SUDAH CETAK', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'WP', 'keterangan' => 'WAJIB DI PILIH', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'SMIN', 'keterangan' => 'NILAI STOK MINUS', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'WISP', 'keterangan' => 'WAJIB ISI SUPPLIER ATAU PELANGGAN', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'PSP', 'keterangan' => 'HANYA BOLEH PILIH SALAH SATU, PELANGGAN ATAU SUPPLIER', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'EMAIL', 'keterangan' => 'HARUS ALAMAT E-MAIL YANG VALID', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'GT-ANGKA-0', 'keterangan' => 'NILAI HARUS LEBIH BESAR DARI 0', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'MIN', 'keterangan' => 'HARUS DIBAWAH', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'BTSANGKA', 'keterangan' => 'ANGKA', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'BATASNILAI', 'keterangan' => 'HARUS', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'MAX', 'keterangan' => 'HARUS DIATAS', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'TSF', 'keterangan' => 'ISIAN TIDAK SESUAI FORMAT', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'NBPT', 'keterangan' => 'NOMINAL BAYAR TIDAK BOLEH MELEBIHI NOMINAL PINJAMAN', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'NTC', 'keterangan' => 'NILAI TIDAK COCOK', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'FXLS', 'keterangan' => 'HARUS BERTIPE XLS ATAU XLSX', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'SATL', 'keterangan' => 'PROSES TIDAK BISA LANJUT KARENA SUDAH DIPAKAI DI TRANSAKSI LAIN', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'STM', 'keterangan' => 'SISA TIDAK BOLEH MINUS', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'TDT', 'keterangan' => 'TRANSAKSI BERASAL DARI INPUTAN TRANSAKSI LAIN', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'ETS', 'keterangan' => 'HANYA BISA EDIT/DELETE DI TANGGAL YANG SAMA', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'TBT', 'keterangan' => 'TIDAK BISA MEMILIH TANGGAL TERSEBUT', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'TSTB', 'keterangan' => 'TANGGAL SUDAH TIDAK BERLAKU', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'TSPTB', 'keterangan' => 'TANGGAL TIDAK BISA DI PROSES  SEBELUM TANGGAL TUTUP BUKU', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'SPIT', 'keterangan' => 'SUDAH PERNAH DI INPUT DI TRADO', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'HF', 'keterangan' => 'FORMAT JAM SALAH', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'TAB', 'keterangan' => 'TIDAK ADA ABSENSI. SILAHKAN INPUT ABSENSI', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'DF', 'keterangan' => 'FORMAT TANGGAL SALAH', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'ENTER', 'keterangan' => 'TEKAN ENTER PADA CELL UNTUK MENYIMPAN PERUBAHAN', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'WG', 'keterangan' => 'FILE HARUS BERUPA GAMBAR', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'TBMINUS', 'keterangan' => 'NILAI TIDAK BOLEH MINUS', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'NTM', 'keterangan' => 'NILAI TIDAK BOLEH MINUS', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'NTLK', 'keterangan' => 'NILAI TIDAK BOLEH < DARI', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'NTLB', 'keterangan' => 'NILAI TIDAK BOLEH > DARI', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'HPDL', 'keterangan' => 'HARAP PILIH DARI LIST', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'FTTS', 'keterangan' => 'FORMAT TANGGAL TIDAK SESUAI DENGAN', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'TVD', 'keterangan' => 'DATA YANG DIMASUKAN TIDAK VALID', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'TTLK', 'keterangan' => 'TANGGAL TIDAK BOLEH < DARI', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'HSD', 'keterangan' => 'HARUS SAMA DENGAN', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'HDSD', 'keterangan' => 'HARUS DIATAS ATAU SAMA DENGAN', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'STGG', 'keterangan' => 'SALAHSATU DARI TRADO, GUDANG ATAU GANDENGAN WAJIB ISI', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'STGGD', 'keterangan' => 'SALAHSATU TRADO DARI, GUDANG DARI ATAU GANDENGAN DARI WAJIB ISI', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'STGGK', 'keterangan' => 'SALAHSATU TRADO KE, GUDANG KE ATAU GANDENGAN KE WAJIB ISI', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'HBSD', 'keterangan' => 'HARUS DIBAWAH ATAU SAMA DENGAN', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'DDTA', 'keterangan' => 'DATA DETAIL TIDAK ADA', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'URBA', 'keterangan' => 'UPAH RITASI BELUM ADA', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'PSB', 'keterangan' => 'HARAP PILIH SALAH SATU BARIS	', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'DTA', 'keterangan' => 'DATA TIDAK ADA', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'BAED', 'keterangan' => 'BUKA APPROVAL EDIT DATA', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'TEPT', 'keterangan' => 'DATA TIDAK BISA DIEDIT PADA TANGGAL INI', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'NTS', 'keterangan' => 'NILAI TIDAK BOLEH SAMA', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'JTK', 'keterangan' => 'TIDAK BOLEH KURANG DARI JUMLAH TRIP YANG SUDAH DIBUAT', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'TOP', 'keterangan' => 'MELEBIHI BATAS TOP AGEN', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'TBA', 'keterangan' => 'NOMINAL TARIF BELUM ADA', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'SIK', 'keterangan' => 'SUDAH INPUT KOMISI', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'TED', 'keterangan' => 'TRANSAKSI TIDAK BISA DIEDIT', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'JOTS', 'keterangan' => 'JENIS ORDERAN TIDAK SAMA DENGAN DETAIL', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'MDB', 'keterangan' => 'MAX. 2 BULAN', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'TBD', 'keterangan' => 'TIDAK BOLEH BERBEDA', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'NDP', 'keterangan' => 'KOSONGKAN NOMINAL LEBIH BAYAR DAN TIPE NOTA DEBET UNTUK MENGGUNAKAN PELUNASAN NOTA DEBET', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'PTS', 'keterangan' => 'PENYESUAIAN TIDAK SAMA', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'TB-0', 'keterangan' => 'TIDAK BOLEH 0', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'POST', 'keterangan' => 'YANG SUDAH DIPOSTING TIDAK DAPAT DIUBAH MENJADI BUKAN POSTING', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'TBI', 'keterangan' => 'TIDAK BOLEH DIISI', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'TBS', 'keterangan' => 'TIDAK BOLEH SAMA', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'STB', 'keterangan' => 'SUDAH TUTUP BUKU', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'SMBE', 'keterangan' => 'SUDAH MELEWATI BATAS EDIT', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'SDE', 'keterangan' => 'SEDANG DIEDIT', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'BPI', 'keterangan' => 'BELUM PERNAH INPUT', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'SPM', 'keterangan' => 'SUDAH PEMUTIHAN. SILAHKAN BUAT SUPIR BARU', 'modifiedby' => 'ADMIN',]);
        error::create(['kodeerror' => 'TSP', 'keterangan' => 'TIDAK SESUAI PARENT', 'modifiedby' => 'ADMIN',]);

    }
}
