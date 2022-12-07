<?php

namespace Database\Seeders;

use App\Models\Parameter;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ParameterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Parameter::truncate();
        DB::statement("DBCC CHECKIDENT ('Parameter', RESEED, 1);");

        Parameter::create(['grp' => 'STATUS AKTIF', 'subgrp' => 'STATUS AKTIF', 'kelompok' => '', 'text' => 'AKTIF', 'memo' => 'UNTUK STATUS AKTIF', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS AKTIF', 'subgrp' => 'STATUS AKTIF', 'kelompok' => '', 'text' => 'NON AKTIF', 'memo' => 'UNTUK STATUS NON AKTIF', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS APPROVAL', 'subgrp' => 'STATUS APPROVAL', 'kelompok' => '', 'text' => 'APPROVAL', 'memo' => 'DI SETUJUI', 'type' => '0', 'singkatan' => 'S', 'warna' => '#28A745', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS APPROVAL', 'subgrp' => 'STATUS APPROVAL', 'kelompok' => '', 'text' => 'NON APPROVAL', 'memo' => 'BELUM DI SETUJUI', 'type' => '0', 'singkatan' => 'BS', 'warna' => '#6C757D', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'ABSENSI', 'subgrp' => 'ABSENSI', 'kelompok' => '', 'text' => '#ABS #9999#/#R#/#Y', 'memo' => 'UNTUK PENOMORAN ABSENSI', 'type' => '118', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS KARYAWAN', 'subgrp' => 'STATUS KARYAWAN', 'kelompok' => '', 'text' => 'KARYAWAN', 'memo' => 'KARYAWAN', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS KARYAWAN', 'subgrp' => 'STATUS KARYAWAN', 'kelompok' => '', 'text' => 'NON KARYAWAN', 'memo' => 'NON KARYAWAN', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'COA', 'subgrp' => 'AP', 'kelompok' => '', 'text' => 'AP', 'memo' => 'AP', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'COA', 'subgrp' => 'AP', 'kelompok' => '', 'text' => 'NON AP', 'memo' => 'NON AP', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'COA', 'subgrp' => 'NERACA', 'kelompok' => '', 'text' => 'NERACA', 'memo' => 'NERACA', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'COA', 'subgrp' => 'NERACA', 'kelompok' => '', 'text' => 'NON NERACA', 'memo' => 'NON NERACA', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'COA', 'subgrp' => 'LABA RUGI', 'kelompok' => '', 'text' => 'LABA RUGI', 'memo' => 'LABA RUGI', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'COA', 'subgrp' => 'LABA RUGI', 'kelompok' => '', 'text' => 'NON LABA RUGI', 'memo' => 'NON LABA RUGI', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS KENDARAAN', 'subgrp' => 'STATUS KENDARAAN', 'kelompok' => '', 'text' => 'KENDARAAN', 'memo' => 'KENDARAAN', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS KENDARAAN', 'subgrp' => 'STATUS KENDARAAN', 'kelompok' => '', 'text' => 'NON KENDARAAN', 'memo' => 'NON KENDARAAN', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS STANDARISASI', 'subgrp' => 'STATUS STANDARISASI', 'kelompok' => '', 'text' => 'STANDARISASI', 'memo' => 'STANDARISASI', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS STANDARISASI', 'subgrp' => 'STATUS STANDARISASI', 'kelompok' => '', 'text' => 'NON STANDARISASI', 'memo' => 'NON STANDARISASI', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'JENIS GANDENGAN', 'subgrp' => 'JENIS GANDENGAN', 'kelompok' => '', 'text' => '20 FEET', 'memo' => '20 FEET', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'JENIS GANDENGAN', 'subgrp' => 'JENIS GANDENGAN', 'kelompok' => '', 'text' => '40 FEET', 'memo' => '40 FEET', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'JENIS PLAT', 'subgrp' => 'JENIS PLAT', 'kelompok' => '', 'text' => 'PLAT KUNING', 'memo' => 'PLAT KUNING', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'JENIS PLAT', 'subgrp' => 'JENIS PLAT', 'kelompok' => '', 'text' => 'PLAT HITAM', 'memo' => 'PLAT HITAM', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS MUTASI', 'subgrp' => 'STATUS MUTASI', 'kelompok' => '', 'text' => 'MUTASI', 'memo' => 'MUTASI', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS MUTASI', 'subgrp' => 'STATUS MUTASI', 'kelompok' => '', 'text' => 'NON MUTASI', 'memo' => 'NON MUTASI', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS VALIDASI KENDARAAN', 'subgrp' => 'STATUS VALIDASI KENDARAAN', 'kelompok' => '', 'text' => 'VALIDASI KENDARAAN', 'memo' => 'VALIDASI KENDARAAN', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS VALIDASI KENDARAAN', 'subgrp' => 'STATUS VALIDASI KENDARAAN', 'kelompok' => '', 'text' => 'NON VALIDASI KENDARAAN', 'memo' => 'NON VALIDASI KENDARAAN', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS MOBIL STORING', 'subgrp' => 'STATUS MOBIL STORING', 'kelompok' => '', 'text' => 'MOBIL STORING', 'memo' => 'MOBIL STORING', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS MOBIL STORING', 'subgrp' => 'STATUS MOBIL STORING', 'kelompok' => '', 'text' => 'NON MOBIL STORING', 'memo' => 'NON MOBIL STORING', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS APPROVAL EDIT BAN', 'subgrp' => 'STATUS APPROVAL EDIT BAN', 'kelompok' => '', 'text' => 'APPROVAL EDIT BAN', 'memo' => 'APPROVAL EDIT BAN', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS APPROVAL EDIT BAN', 'subgrp' => 'STATUS APPROVAL EDIT BAN', 'kelompok' => '', 'text' => 'UNAPPROVAL EDIT BAN', 'memo' => 'UNAPPROVAL EDIT BAN', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS LEWAT VALIDASI', 'subgrp' => 'STATUS LEWAT VALIDASI', 'kelompok' => '', 'text' => 'APPROVAL LEWAT VALIDASI', 'memo' => 'APPROVAL LEWAT VALIDASI', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS LEWAT VALIDASI', 'subgrp' => 'STATUS LEWAT VALIDASI', 'kelompok' => '', 'text' => 'UNAPPROVAL LEWAT VALIDASI', 'memo' => 'UNAPPROVAL LEWAT VALIDASI', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'PENERIMAAN KAS', 'subgrp' => 'NOMOR PENERIMAAN KAS', 'kelompok' => 'PENERIMAAN BANK', 'text' => '#KMT #9999#/#R#/#Y', 'memo' => 'NOMOR PENERIMAAN KAS', 'type' => '118', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'PENGELUARAN KAS', 'subgrp' => 'NOMOR  PENGELUARAN KAS', 'kelompok' => 'PENGELUARAN BANK', 'text' => '#KBT #9999#/#R#/#Y', 'memo' => 'NOMOR PENGELUARAN KAS', 'type' => '118', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS ACCOUNT PAYABLE', 'subgrp' => 'STATUS ACCOUNT PAYABLE', 'kelompok' => '', 'text' => 'STATUS AKTIF ACCOUNT PAYABLE', 'memo' => 'STATUS AKTIF ACCOUNT PAYABLE', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS ACCOUNT PAYABLE', 'subgrp' => 'STATUS ACCOUNT PAYABLE', 'kelompok' => '', 'text' => 'STATUS NON AKTIF ACCOUNT PAYABLE', 'memo' => 'STATUS NON AKTIF ACCOUNT PAYABLE', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS NERACA', 'subgrp' => 'STATUS NERACA', 'kelompok' => '', 'text' => 'STATUS AKTIF NERACA', 'memo' => 'STATUS AKTIF NERACA', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS NERACA', 'subgrp' => 'STATUS NERACA', 'kelompok' => '', 'text' => 'STATUS NON AKTIF NERACA', 'memo' => 'STATUS NON AKTIF NERACA', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS LABA RUGI', 'subgrp' => 'STATUS LABA RUGI', 'kelompok' => '', 'text' => 'STATUS AKTIF LABA RUGI', 'memo' => 'STATUS AKTIF LABA RUGI', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS LABA RUGI', 'subgrp' => 'STATUS LABA RUGI', 'kelompok' => '', 'text' => 'STATUS NON AKTIF LABA RUGI', 'memo' => 'STATUS NON AKTIF LABA RUGI', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'SISTEM TON', 'subgrp' => 'SISTEM TON', 'kelompok' => '', 'text' => 'AKTIF SISTEM TON', 'memo' => 'AKTIF SISTEM TON', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'SISTEM TON', 'subgrp' => 'SISTEM TON', 'kelompok' => '', 'text' => 'NON AKTIF SISTEM TON', 'memo' => 'NON AKTIF SISTEM TON', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'PENYESUAIAN HARGA', 'subgrp' => 'PENYESUAIAN HARGA', 'kelompok' => '', 'text' => 'PENYESUAIAN HARGA', 'memo' => 'PENYESUAIAN HARGA', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'PENYESUAIAN HARGA', 'subgrp' => 'PENYESUAIAN HARGA', 'kelompok' => '', 'text' => 'BUKAN PENYESUAIAN HARGA', 'memo' => 'BUKAN PENYESUAIAN HARGA', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS ADA UPDATE GAMBAR', 'subgrp' => 'STATUS ADA UPDATE GAMBAR', 'kelompok' => '', 'text' => 'WAJIB ADA UPDATE GAMBAR', 'memo' => 'WAJIB ADA UPDATE GAMBAR', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS ADA UPDATE GAMBAR', 'subgrp' => 'STATUS ADA UPDATE GAMBAR', 'kelompok' => '', 'text' => 'TIDAK WAJIB ADA UPDATE GAMBAR', 'memo' => 'TIDAK WAJIB ADA UPDATE GAMBAR', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS LUAR KOTA', 'subgrp' => 'STATUS LUAR KOTA', 'kelompok' => '', 'text' => 'BOLEH LUAR KOTA', 'memo' => 'BOLEH LUAR KOTA', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS LUAR KOTA', 'subgrp' => 'STATUS LUAR KOTA', 'kelompok' => '', 'text' => 'TIDAK BOLEH LUAR KOTA', 'memo' => 'TIDAK BOLEH LUAR KOTA', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'ZONA TERTENTU', 'subgrp' => 'ZONA TERTENTU', 'kelompok' => '', 'text' => 'HARUS ZONA TERTENTU', 'memo' => 'HARUS ZONA TERTENTU', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'ZONA TERTENTU', 'subgrp' => 'ZONA TERTENTU', 'kelompok' => '', 'text' => 'TIDAK HARUS KE ZONA TERTENTU', 'memo' => 'TIDAK HARUS KE ZONA TERTENTU', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'BLACKLIST SUPIR', 'subgrp' => 'BLACKLIST SUPIR', 'kelompok' => '', 'text' => 'SUPIR BLACKLIST', 'memo' => 'SUPIR BLACKLIST', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'BLACKLIST SUPIR', 'subgrp' => 'BLACKLIST SUPIR', 'kelompok' => '', 'text' => 'BUKAN SUPIR BLACKLIST', 'memo' => 'BUKAN SUPIR BLACKLIST', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'KAS GANTUNG', 'subgrp' => 'NOMOR KAS GANTUNG', 'kelompok' => '', 'text' => '#KGT #9999#/#R#/#Y', 'memo' => 'FORMAT NOMOR KAS GANTUNG', 'type' => '118', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'PROSES ABSENSI', 'subgrp' => 'NOMOR PROSES ABSENSI', 'kelompok' => '', 'text' => '#PAB #9999#/#R#/#Y', 'memo' => 'FORMAT NOMOR PROSES ABSENSI', 'type' => '118', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'JENIS TRANSAKSI', 'subgrp' => 'JENIS TRANSAKSI', 'kelompok' => '', 'text' => 'KAS', 'memo' => 'JENIS TRANSAKSI KAS', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'JENIS TRANSAKSI', 'subgrp' => 'JENIS TRANSAKSI', 'kelompok' => '', 'text' => 'BANK', 'memo' => 'JENIS TRANSAKSI BANK', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS LANGSUNG CAIR', 'subgrp' => 'STATUS LANGSUNG CAIR', 'kelompok' => '', 'text' => 'LANGSUNG CAIR', 'memo' => 'LANGSUNG CAIR', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS LANGSUNG CAIR', 'subgrp' => 'STATUS LANGSUNG CAIR', 'kelompok' => '', 'text' => 'TIDAK LANGSUNG CAIR', 'memo' => 'TIDAK LANGSUNG CAIR', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS DEFAULT', 'subgrp' => 'STATUS DEFAULT', 'kelompok' => '', 'text' => 'DEFAULT', 'memo' => 'DEFAULT', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS DEFAULT', 'subgrp' => 'STATUS DEFAULT', 'kelompok' => '', 'text' => 'BUKAN DEFAULT', 'memo' => 'BUKAN DEFAULT', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'LUAR KOTA', 'subgrp' => 'LUAR KOTA', 'kelompok' => '', 'text' => 'LUAR KOTA', 'memo' => 'LUAR KOTA', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'DALAM KOTA', 'subgrp' => 'DALAM KOTA', 'kelompok' => '', 'text' => 'DALAM KOTA', 'memo' => 'DALAM KOTA', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS COA', 'subgrp' => 'STATUS COA', 'kelompok' => '', 'text' => 'BANK', 'memo' => 'STATUS COA LINK KE MASTER BANK', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS COA', 'subgrp' => 'STATUS COA', 'kelompok' => '', 'text' => 'ALL', 'memo' => 'UNTUK SEMUA', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'SURAT PENGANTAR', 'subgrp' => 'SURAT PENGANTAR', 'kelompok' => '', 'text' => '#TRP #9999#/#R#/#Y', 'memo' => 'SURAT PENGANTAR', 'type' => '118', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS LONGTRIP', 'subgrp' => 'STATUS LONGTRIP', 'kelompok' => '', 'text' => 'LONGTRIP', 'memo' => 'LONGTRIP', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS LONGTRIP', 'subgrp' => 'STATUS LONGTRIP', 'kelompok' => '', 'text' => 'BUKAN LONGTRIP', 'memo' => 'BUKAN LONGTRIP', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS PERALIHAN', 'subgrp' => 'STATUS PERALIHAN', 'kelompok' => '', 'text' => 'PERALIHAN', 'memo' => 'PERALIHAN', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS PERALIHAN', 'subgrp' => 'STATUS PERALIHAN', 'kelompok' => '', 'text' => 'BUKAN PERALIHAN', 'memo' => 'BUKAN PERALIHAN', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS CETAK', 'subgrp' => 'STATUS CETAK', 'kelompok' => '', 'text' => 'SUDAH CETAK', 'memo' => 'SUDAH CETAK', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS CETAK', 'subgrp' => 'STATUS CETAK', 'kelompok' => '', 'text' => 'BELUM CETAK', 'memo' => 'BELUM CETAK', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS NOTIF', 'subgrp' => 'STATUS NOTIF', 'kelompok' => '', 'text' => 'NOTIF', 'memo' => 'NOTIF', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS NOTIF', 'subgrp' => 'STATUS NOTIF', 'kelompok' => '', 'text' => 'TIDAK ADA NOTIF', 'memo' => 'TIDAK ADA NOTIF', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS ONE WAY', 'subgrp' => 'STATUS ONE WAY', 'kelompok' => '', 'text' => 'ONE WAY', 'memo' => 'ONE WAY', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS ONE WAY', 'subgrp' => 'STATUS ONE WAY', 'kelompok' => '', 'text' => 'BUKAN ONE WAY', 'memo' => 'BUKAN ONE WAY', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS EDIT TUJUAN', 'subgrp' => 'STATUS EDIT TUJUAN', 'kelompok' => '', 'text' => 'EDIT TUJUAN', 'memo' => 'EDIT TUJUAN', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS EDIT TUJUAN', 'subgrp' => 'STATUS EDIT TUJUAN', 'kelompok' => '', 'text' => 'TIDAK BOLEH EDIT TUJUAN', 'memo' => 'TIDAK BOLEH EDIT TUJUAN', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS TRIP', 'subgrp' => 'STATUS TRIP', 'kelompok' => '', 'text' => 'ADA TRIP ASAL', 'memo' => 'ADA TRIP ASAL', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS TRIP', 'subgrp' => 'STATUS TRIP', 'kelompok' => '', 'text' => 'TIDAK ADA TRIP ASAL', 'memo' => 'TIDAK ADA TRIP ASAL', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS LANGSIR', 'subgrp' => 'STATUS LANGSIR', 'kelompok' => '', 'text' => 'LANGSIR', 'memo' => 'LANGSIR', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS LANGSIR', 'subgrp' => 'STATUS LANGSIR', 'kelompok' => '', 'text' => 'BUKAN LANGSIR', 'memo' => 'BUKAN LANGSIR', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS BERKAS', 'subgrp' => 'STATUS BERKAS', 'kelompok' => '', 'text' => 'ADA BERKAS', 'memo' => 'ADA BERKAS', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS BERKAS', 'subgrp' => 'STATUS BERKAS', 'kelompok' => '', 'text' => 'TIDAK ADA BERKAS', 'memo' => 'TIDAK ADA BERKAS', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS POSTING', 'subgrp' => 'STATUS POSTING', 'kelompok' => '', 'text' => 'POSTING', 'memo' => 'POSTING', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS POSTING', 'subgrp' => 'STATUS POSTING', 'kelompok' => '', 'text' => 'BUKAN POSTING', 'memo' => 'BUKAN POSTING', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS INVOICE', 'subgrp' => 'STATUS INVOICE', 'kelompok' => '', 'text' => 'INVOICE UTAMA', 'memo' => 'INVOICE UTAMA', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS INVOICE', 'subgrp' => 'STATUS INVOICE', 'kelompok' => '', 'text' => 'INVOICE TAMBAHAN', 'memo' => 'INVOICE TAMBAHAN', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'PENERIMAAN BANK BCA-1', 'subgrp' => 'PENERIMAAN BANK BCA-1', 'kelompok' => 'PENERIMAAN BANK', 'text' => '#|BMT-M BCA |# 9999#/#R#/#Y', 'memo' => 'PENERIMAAN BANK BCA-1', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'PENGELUARAN BANK BCA-1', 'subgrp' => 'PENGELUARAN BANK BCA-1', 'kelompok' => 'PENGELUARAN BANK', 'text' => '#|BKT-M BCA |# 9999#/#R#/#Y', 'memo' => 'PENGELUARAN BANK BCA-1', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS RITASI', 'subgrp' => 'STATUS RITASI', 'kelompok' => '', 'text' => 'NAIK KEPALA', 'memo' => 'NAIK KEPALA', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS RITASI', 'subgrp' => 'STATUS RITASI', 'kelompok' => '', 'text' => 'TURUN KEPALA', 'memo' => 'TURUN KEPALA', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS RITASI', 'subgrp' => 'STATUS RITASI', 'kelompok' => '', 'text' => 'PULANG RANGKA', 'memo' => 'PULANG RANGKA', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS RITASI', 'subgrp' => 'STATUS RITASI', 'kelompok' => '', 'text' => 'TURUN RANGKA', 'memo' => 'TURUN RANGKA', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS RITASI', 'subgrp' => 'STATUS RITASI', 'kelompok' => '', 'text' => 'POTONG GANDENGAN', 'memo' => 'POTONG GANDENGAN', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS BAN', 'subgrp' => 'STATUS BAN', 'kelompok' => '', 'text' => 'STOK BAN', 'memo' => 'STOK BAN', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS BAN', 'subgrp' => 'STATUS BAN', 'kelompok' => '', 'text' => 'BUKAN STOK BAN', 'memo' => 'BUKAN STOK BAN', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS DAFTAR HARGA', 'subgrp' => 'STATUS DAFTAR HARGA', 'kelompok' => '', 'text' => 'BERSEDIA', 'memo' => 'BERSEDIA', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS DAFTAR HARGA', 'subgrp' => 'STATUS DAFTAR HARGA', 'kelompok' => '', 'text' => 'TIDAK BERSEDIA', 'memo' => 'TIDAK BERSEDIA', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS TAS', 'subgrp' => 'STATUS TAS', 'kelompok' => '', 'text' => 'TAS', 'memo' => 'TAS', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS TAS', 'subgrp' => 'STATUS TAS', 'kelompok' => '', 'text' => 'BUKAN TAS', 'memo' => 'BUKAN TAS', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS GUDANG', 'subgrp' => 'STATUS GUDANG', 'kelompok' => '', 'text' => 'KANTOR', 'memo' => '', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => '1',]);
        Parameter::create(['grp' => 'STATUS GUDANG', 'subgrp' => 'STATUS GUDANG', 'kelompok' => '', 'text' => 'SEMENTARA', 'memo' => '', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => '1',]);
        Parameter::create(['grp' => 'STATUS PENYESUAIAN HARGA', 'subgrp' => 'STATUS PENYESUAIAN HARGA', 'kelompok' => '', 'text' => 'OK', 'memo' => '', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => '1',]);
        Parameter::create(['grp' => 'ORDERANTRUCKING', 'subgrp' => 'ORDERANTRUCKING', 'kelompok' => '', 'text' => '9999#/#R#/#Y', 'memo' => '', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => '1',]);
        Parameter::create(['grp' => '', 'subgrp' => '', 'kelompok' => '', 'text' => '', 'memo' => '', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => '1',]);
        Parameter::create(['grp' => '', 'subgrp' => '', 'kelompok' => '', 'text' => '', 'memo' => '', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => '1',]);
        Parameter::create(['grp' => 'PROSESABSENSISUPIR', 'subgrp' => 'PROSESABSENSISUPIR', 'kelompok' => '', 'text' => '#PAS #9999#/#R#/#Y', 'memo' => '', 'type' => '118', 'singkatan' => '', 'warna' => '', 'modifiedby' => '1',]);
        Parameter::create(['grp' => '', 'subgrp' => '', 'kelompok' => '', 'text' => '', 'memo' => '', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => '1',]);
        Parameter::create(['grp' => '', 'subgrp' => '', 'kelompok' => '', 'text' => '', 'memo' => '', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => '1',]);
        Parameter::create(['grp' => '', 'subgrp' => '', 'kelompok' => '', 'text' => '', 'memo' => '', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => '1',]);
        Parameter::create(['grp' => 'COA KAS GANTUNG', 'subgrp' => 'COA KAS GANTUNG', 'kelompok' => '', 'text' => '01.01.02.00', 'memo' => 'COA KAS GANTUNG', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => '1',]);
        Parameter::create(['grp' => '', 'subgrp' => '', 'kelompok' => '', 'text' => '', 'memo' => '', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => '1',]);
        Parameter::create(['grp' => '', 'subgrp' => '', 'kelompok' => '', 'text' => '', 'memo' => '', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => '1',]);
        Parameter::create(['grp' => 'RITASI', 'subgrp' => 'RITASI', 'kelompok' => '', 'text' => '#RTT #9999#/#R#/#Y', 'memo' => '', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => '1',]);
        Parameter::create(['grp' => '', 'subgrp' => '', 'kelompok' => '', 'text' => '', 'memo' => '', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => '1',]);
        Parameter::create(['grp' => 'JURNAL UMUM BUKTI', 'subgrp' => 'JURNAL UMUM BUKTI', 'kelompok' => '', 'text' => '9999#/#R#/#Y#ADJ #', 'memo' => 'JURNAL UMUM', 'type' => '118', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS KAS', 'subgrp' => 'STATUS KAS', 'kelompok' => '', 'text' => 'KAS', 'memo' => 'KAS', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS KAS', 'subgrp' => 'STATUS KAS', 'kelompok' => '', 'text' => 'BUKAN STATUS KAS', 'memo' => 'BUKAN STATUS KAS', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'RESET PENOMORAN', 'subgrp' => 'RESET PENOMORAN', 'kelompok' => '', 'text' => 'RESET BULAN', 'memo' => 'RESET BULAN', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'RESET PENOMORAN', 'subgrp' => 'RESET PENOMORAN', 'kelompok' => '', 'text' => 'RESET TAHUN', 'memo' => 'RESET TAHUN', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'COA PIUTANG MANUAL', 'subgrp' => 'DEBET', 'kelompok' => '', 'text' => '49', 'memo' => 'COA PIUTANG MANUAL DEBET', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'COA PIUTANG MANUAL', 'subgrp' => 'KREDIT', 'kelompok' => '', 'text' => '138', 'memo' => 'COA PIUTANG MANUAL KREDIT', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'PENGELUARAN TRUCKING', 'subgrp' => 'PINJAMAN SUPIR BUKTI', 'kelompok' => '', 'text' => '#PJT #9999#/#R#/#Y', 'memo' => 'PINJAMAN SUPIR BUKTI', 'type' => '118', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'PENGELUARAN TRUCKING', 'subgrp' => 'BIAYA LAIN SUPIR BUKTI', 'kelompok' => '', 'text' => '#BLS #9999#/#R#/#Y', 'memo' => 'BIAYA LAIN SUPIR BUKTI', 'type' => '118', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'PIUTANG BUKTI', 'subgrp' => 'PIUTANG BUKTI', 'kelompok' => '', 'text' => '#EPT #9999#/#R#/#Y', 'memo' => 'PIUTANG BUKTI', 'type' => '118', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'PENERIMAAN TRUCKING', 'subgrp' => 'DEPOSITO SUPIR BUKTI', 'kelompok' => '', 'text' => '#DPO #9999#/#R#/#Y', 'memo' => 'PIUTANG BUKTI', 'type' => '118', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'PENERIMAAN TRUCKING', 'subgrp' => 'PENGEMBALIAN PINJAMAN SUPIR BUKTI', 'kelompok' => '', 'text' => '#PJP #9999#/#R#/#Y', 'memo' => 'PIUTANG BUKTI', 'type' => '118', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'HUTANG BUKTI', 'subgrp' => 'HUTANG BUKTI', 'kelompok' => '', 'text' => '#EHT #9999#/#R#/#Y', 'memo' => 'HUTANG BUKTI', 'type' => '118', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'PELUNASAN PIUTANG BUKTI', 'subgrp' => 'PELUNASAN PIUTANG BUKTI', 'kelompok' => '', 'text' => '#PPT #9999#/#R#/#Y', 'memo' => 'PELUNASAN PIUTANG BUKTI', 'type' => '118', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'PEMBAYARAN HUTANG BUKTI', 'subgrp' => 'PEMBAYARAN HUTANG BUKTI', 'kelompok' => '', 'text' => '#PHT #9999#/#R#/#Y', 'memo' => 'PEMBAYARAN HUTANG BUKTI', 'type' => '118', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'COA HUTANG MANUAL', 'subgrp' => 'DEBET', 'kelompok' => '', 'text' => '88', 'memo' => 'COA HUTANG MANUAL DEBET', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'COA HUTANG MANUAL', 'subgrp' => 'KREDIT', 'kelompok' => '', 'text' => '96', 'memo' => 'COA HUTANG MANUAL KREDIT', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'PENERIMAAN STOK', 'subgrp' => 'DELIVERY ORDER BUKTI', 'kelompok' => '', 'text' => '#DOT #9999#/#R#/#Y', 'memo' => 'DELIVERY ORDER BUKTI', 'type' => '118', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'PENERIMAAN STOK', 'subgrp' => 'PO STOK BUKTI', 'kelompok' => '', 'text' => '#POT #9999#/#R#/#Y', 'memo' => 'PO STOK BUKTI', 'type' => '118', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'PENERIMAAN STOK', 'subgrp' => 'BELI STOK BUKTI', 'kelompok' => '', 'text' => '#PBT #9999#/#R#/#Y', 'memo' => 'BELI STOK BUKTI', 'type' => '118', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'PENGELUARAN STOK', 'subgrp' => 'SPK STOK BUKTI', 'kelompok' => '', 'text' => '#SPK #9999#/#R#/#Y', 'memo' => 'SPK STOK BUKTI', 'type' => '118', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'PENERIMAAN STOK', 'subgrp' => 'KOREKSI STOK BUKTI', 'kelompok' => '', 'text' => '#KST #9999#/#R#/#Y', 'memo' => 'KOREKSI STOK BUKTI', 'type' => '118', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'PENERIMAAN STOK', 'subgrp' => 'PINDAH GUDANG BUKTI', 'kelompok' => '', 'text' => '#PGT #9999#/#R#/#Y', 'memo' => 'PINDAH GUDANG BUKTI', 'type' => '118', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'PENERIMAAN STOK', 'subgrp' => 'PERBAIKAN STOK BUKTI', 'kelompok' => '', 'text' => '#PST #9999#/#R#/#Y', 'memo' => 'PERBAIKAN STOK BUKTI', 'type' => '118', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'PENGELUARAN STOK', 'subgrp' => 'RETUR BELI BUKTI', 'kelompok' => '', 'text' => '#RBT #9999#/#R#/#Y', 'memo' => 'RETUR BELI BUKTI', 'type' => '118', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => '', 'subgrp' => '', 'kelompok' => '', 'text' => '', 'memo' => 'STATUS HITUNG STOK', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'PENCAIRAN GIRO BUKTI', 'subgrp' => 'PENCAIRAN GIRO BUKTI', 'kelompok' => '', 'text' => '#PGBT #9999#/#R#/#Y', 'memo' => 'PENCAIRAN GIRO BUKTI', 'type' => '118', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'SERVICE IN BUKTI', 'subgrp' => 'SERVICE IN BUKTI', 'kelompok' => '', 'text' => '#SIN #9999#/#R#/#Y', 'memo' => 'SERVICE IN BUKTI', 'type' => '118', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'SERVICE OUT BUKTI', 'subgrp' => 'SERVICE OUT BUKTI', 'kelompok' => '', 'text' => '#SOU #9999#/#R#/#Y', 'memo' => 'SERVICE OUT BUKTI', 'type' => '118', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'ABSENSI SUPIR APPROVAL BUKTI', 'subgrp' => 'ABSENSI SUPIR APPROVAL BUKTI', 'kelompok' => '', 'text' => '#ASA #9999#/#R#/#Y', 'memo' => 'SERVICE OUT BUKTI', 'type' => '118', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'PENERIMAAN STOK', 'subgrp' => 'SALDO STOK BUKTI', 'kelompok' => '', 'text' => '#SST #9999#/#R#/#Y', 'memo' => 'SALDO STOK BUKTI', 'type' => '118', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'RINCIAN GAJI SUPIR BUKTI', 'subgrp' => 'RINCIAN GAJI SUPIR BUKTI', 'kelompok' => '', 'text' => '#RIC #9999#/#R#/#Y', 'memo' => 'RINCIAN GAJI SUPIR BUKTI', 'type' => '118', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'PENGEMBALIAN KAS GANTUNG BUKTI', 'subgrp' => 'PENGEMBALIAN KAS GANTUNG BUKTI', 'kelompok' => '', 'text' => '#PKG #9999#/#R#/#Y', 'memo' => 'PENGEMBALIAN KAS GANTUNG BUKTI', 'type' => '118', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'PROSES GAJI SUPIR BUKTI', 'subgrp' => 'PROSES GAJI SUPIR BUKTI', 'kelompok' => '', 'text' => '#EBS #9999#/#R#/#Y', 'memo' => 'PROSES GAJI SUPIR BUKTI', 'type' => '118', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'NOTA KREDIT BUKTI', 'subgrp' => 'NOTA KREDIT BUKTI', 'kelompok' => '', 'text' => '#EBS #9999#/#R#/#Y', 'memo' => 'NOTA KREDIT BUKTI', 'type' => '118', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'NOTA DEBET BUKTI', 'subgrp' => 'NOTA DEBET BUKTI', 'kelompok' => '', 'text' => '#EBS #9999#/#R#/#Y', 'memo' => 'NOTA DEBET BUKTI', 'type' => '118', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'INVOICE BUKTI', 'subgrp' => 'INVOICE BUKTI', 'kelompok' => '', 'text' => '#INV #9999#/#R#/#Y', 'memo' => 'INVOICE BUKTI', 'type' => '118', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'INVOICE EXTRA BUKTI', 'subgrp' => 'INVOICE EXTRA BUKTI', 'kelompok' => '', 'text' => '#EXT #9999#/#R#/#Y', 'memo' => 'INVOICE EXTRA BUKTI', 'type' => '118', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'COA INVOICE DEBET', 'subgrp' => 'COA INVOICE DEBET', 'kelompok' => 'COA INVOICE', 'text' => '01.03.01.02', 'memo' => 'COA INVOICE DEBET', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'COA INVOICE KREDIT', 'subgrp' => 'COA INVOICE KREDIT', 'kelompok' => 'COA INVOICE', 'text' => '06.01.01.02', 'memo' => 'COA INVOICE KREDIT', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'COA APPROVAL ABSENSI SUPIR KREDIT', 'subgrp' => 'COA APPROVAL ABSENSI SUPIR KREDIT', 'kelompok' => 'COA APPROVAL ABSENSI SUPIR', 'text' => '01.01.01.02', 'memo' => 'COA APPROVAL ABSENSI SUPIR KREDIT', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'COA PEMBAYARAN HUTANG DEBET', 'subgrp' => 'COA PEMBAYARAN HUTANG DEBET', 'kelompok' => 'COA PEMBAYARAN HUTANG', 'text' => '03.02.02.01', 'memo' => 'COA PEMBAYARAN HUTANG DEBET', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'COA APPROVAL ABSENSI SUPIR DEBET', 'subgrp' => 'COA APPROVAL ABSENSI SUPIR DEBET', 'kelompok' => 'COA APPROVAL ABSENSI SUPIR', 'text' => '01.01.02.02', 'memo' => 'COA APPROVAL ABSENSI SUPIR DEBET', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'PENERIMAAN GIRO BUKTI', 'subgrp' => 'PENERIMAAN GIRO BUKTI', 'kelompok' => '', 'text' => '#BPGT-M BCA #9999#/#R#/#Y', 'memo' => 'PENERIMAAN GIRO BUKTI', 'type' => '118', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'COA PENERIMAAN GIRO DEBET', 'subgrp' => 'COA PENERIMAAN GIRO DEBET', 'kelompok' => 'COA PENERIMAAN GIRO', 'text' => '01.03.03.00', 'memo' => 'COA PENERIMAAN GIRO DEBET', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'COA PENERIMAAN GIRO KREDIT', 'subgrp' => 'COA PENERIMAAN GIRO KREDIT', 'kelompok' => 'COA PENERIMAAN GIRO', 'text' => '01.03.02.02', 'memo' => 'COA PENERIMAAN GIRO KREDIT', 'type' => '0', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'PENGEMBALIAN KASBANK BUKTI', 'subgrp' => 'PENGEMBALIAN KASBANK BUKTI', 'kelompok' => '', 'text' => '#PKBT #9999#/#R#/#Y', 'memo' => 'PENGEMBALIAN KASBANK BUKTI', 'type' => '118', 'singkatan' => '', 'warna' => '', 'modifiedby' => 'ADMIN',]);
    }
}
