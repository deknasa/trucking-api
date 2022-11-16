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

        parameter::create([ 'grp' => 'STATUS AKTIF', 'subgrp' => 'STATUS AKTIF', 'text' => 'AKTIF', 'memo' => 'UNTUK STATUS AKTIF', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS AKTIF', 'subgrp' => 'STATUS AKTIF', 'text' => 'NON AKTIF', 'memo' => 'UNTUK STATUS NON AKTIF', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS APPROVAL', 'subgrp' => 'STATUS APPROVAL', 'text' => 'APPROVAL', 'memo' => 'APPROVAL', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS APPROVAL', 'subgrp' => 'STATUS APPROVAL', 'text' => 'NON APPROVAL', 'memo' => 'NON APPROVAL', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'ABSENSI', 'subgrp' => 'ABSENSI', 'text' => '#ABS # 9999#/#R#/#Y', 'memo' => 'UNTUK PENOMORAN ABSENSI', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS KARYAWAN', 'subgrp' => 'STATUS KARYAWAN', 'text' => 'KARYAWAN', 'memo' => 'KARYAWAN', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS KARYAWAN', 'subgrp' => 'STATUS KARYAWAN', 'text' => 'NON KARYAWAN', 'memo' => 'NON KARYAWAN', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'COA', 'subgrp' => 'AP', 'text' => 'AP', 'memo' => 'AP', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'COA', 'subgrp' => 'AP', 'text' => 'NON AP', 'memo' => 'NON AP', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'COA', 'subgrp' => 'NERACA', 'text' => 'NERACA', 'memo' => 'NERACA', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'COA', 'subgrp' => 'NERACA', 'text' => 'NON NERACA', 'memo' => 'NON NERACA', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'COA', 'subgrp' => 'LABA RUGI', 'text' => 'LABA RUGI', 'memo' => 'LABA RUGI', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'COA', 'subgrp' => 'LABA RUGI', 'text' => 'NON LABA RUGI', 'memo' => 'NON LABA RUGI', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS KENDARAAN', 'subgrp' => 'STATUS KENDARAAN', 'text' => 'KENDARAAN', 'memo' => 'KENDARAAN', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS KENDARAAN', 'subgrp' => 'STATUS KENDARAAN', 'text' => 'NON KENDARAAN', 'memo' => 'NON KENDARAAN', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS STANDARISASI', 'subgrp' => 'STATUS STANDARISASI', 'text' => 'STANDARISASI', 'memo' => 'STANDARISASI', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS STANDARISASI', 'subgrp' => 'STATUS STANDARISASI', 'text' => 'NON STANDARISASI', 'memo' => 'NON STANDARISASI', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'JENIS GANDENGAN', 'subgrp' => 'JENIS GANDENGAN', 'text' => '20 FEET', 'memo' => '20 FEET', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'JENIS GANDENGAN', 'subgrp' => 'JENIS GANDENGAN', 'text' => '40 FEET', 'memo' => '40 FEET', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'JENIS PLAT', 'subgrp' => 'JENIS PLAT', 'text' => 'PLAT KUNING', 'memo' => 'PLAT KUNING', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'JENIS PLAT', 'subgrp' => 'JENIS PLAT', 'text' => 'PLAT HITAM', 'memo' => 'PLAT HITAM', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS MUTASI', 'subgrp' => 'STATUS MUTASI', 'text' => 'MUTASI', 'memo' => 'MUTASI', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS MUTASI', 'subgrp' => 'STATUS MUTASI', 'text' => 'NON MUTASI', 'memo' => 'NON MUTASI', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS VALIDASI KENDARAAN', 'subgrp' => 'STATUS VALIDASI KENDARAAN', 'text' => 'VALIDASI KENDARAAN', 'memo' => 'VALIDASI KENDARAAN', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS VALIDASI KENDARAAN', 'subgrp' => 'STATUS VALIDASI KENDARAAN', 'text' => 'NON VALIDASI KENDARAAN', 'memo' => 'NON VALIDASI KENDARAAN', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS MOBIL STORING', 'subgrp' => 'STATUS MOBIL STORING', 'text' => 'MOBIL STORING', 'memo' => 'MOBIL STORING', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS MOBIL STORING', 'subgrp' => 'STATUS MOBIL STORING', 'text' => 'NON MOBIL STORING', 'memo' => 'NON MOBIL STORING', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS APPROVAL EDIT BAN', 'subgrp' => 'STATUS APPROVAL EDIT BAN', 'text' => 'APPROVAL EDIT BAN', 'memo' => 'APPROVAL EDIT BAN', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS APPROVAL EDIT BAN', 'subgrp' => 'STATUS APPROVAL EDIT BAN', 'text' => 'UNAPPROVAL EDIT BAN', 'memo' => 'UNAPPROVAL EDIT BAN', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS LEWAT VALIDASI', 'subgrp' => 'STATUS LEWAT VALIDASI', 'text' => 'APPROVAL LEWAT VALIDASI', 'memo' => 'APPROVAL LEWAT VALIDASI', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS LEWAT VALIDASI', 'subgrp' => 'STATUS LEWAT VALIDASI', 'text' => 'UNAPPROVAL LEWAT VALIDASI', 'memo' => 'UNAPPROVAL LEWAT VALIDASI', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'PENERIMAAN KAS', 'subgrp' => 'NOMOR PENERIMAAN KAS', 'text' => '#|KMT |# 9999#/#R#/#Y', 'memo' => 'NOMOR PENERIMAAN KAS', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => 'PENERIMAAN BANK',]);
        parameter::create([ 'grp' => 'PENGELUARAN KAS', 'subgrp' => 'NOMOR  PENGELUARAN KAS', 'text' => '#|KBT |# 9999#/#R#/#Y', 'memo' => 'NOMOR PENGELUARAN KAS', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => 'PENGELUARAN BANK',]);
        parameter::create([ 'grp' => 'STATUS ACCOUNT PAYABLE', 'subgrp' => 'STATUS ACCOUNT PAYABLE', 'text' => 'STATUS AKTIF ACCOUNT PAYABLE', 'memo' => 'STATUS AKTIF ACCOUNT PAYABLE', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS ACCOUNT PAYABLE', 'subgrp' => 'STATUS ACCOUNT PAYABLE', 'text' => 'STATUS NON AKTIF ACCOUNT PAYABLE', 'memo' => 'STATUS NON AKTIF ACCOUNT PAYABLE', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS NERACA', 'subgrp' => 'STATUS NERACA', 'text' => 'STATUS AKTIF NERACA', 'memo' => 'STATUS AKTIF NERACA', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS NERACA', 'subgrp' => 'STATUS NERACA', 'text' => 'STATUS NON AKTIF NERACA', 'memo' => 'STATUS NON AKTIF NERACA', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS LABA RUGI', 'subgrp' => 'STATUS LABA RUGI', 'text' => 'STATUS AKTIF LABA RUGI', 'memo' => 'STATUS AKTIF LABA RUGI', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS LABA RUGI', 'subgrp' => 'STATUS LABA RUGI', 'text' => 'STATUS NON AKTIF LABA RUGI', 'memo' => 'STATUS NON AKTIF LABA RUGI', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'SISTEM TON', 'subgrp' => 'SISTEM TON', 'text' => 'AKTIF SISTEM TON', 'memo' => 'AKTIF SISTEM TON', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'SISTEM TON', 'subgrp' => 'SISTEM TON', 'text' => 'NON AKTIF SISTEM TON', 'memo' => 'NON AKTIF SISTEM TON', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'PENYESUAIAN HARGA', 'subgrp' => 'PENYESUAIAN HARGA', 'text' => 'PENYESUAIAN HARGA', 'memo' => 'PENYESUAIAN HARGA', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'PENYESUAIAN HARGA', 'subgrp' => 'PENYESUAIAN HARGA', 'text' => 'BUKAN PENYESUAIAN HARGA', 'memo' => 'BUKAN PENYESUAIAN HARGA', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS ADA UPDATE GAMBAR', 'subgrp' => 'STATUS ADA UPDATE GAMBAR', 'text' => 'WAJIB ADA UPDATE GAMBAR', 'memo' => 'WAJIB ADA UPDATE GAMBAR', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS ADA UPDATE GAMBAR', 'subgrp' => 'STATUS ADA UPDATE GAMBAR', 'text' => 'TIDAK WAJIB ADA UPDATE GAMBAR', 'memo' => 'TIDAK WAJIB ADA UPDATE GAMBAR', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS LUAR KOTA', 'subgrp' => 'STATUS LUAR KOTA', 'text' => 'BOLEH LUAR KOTA', 'memo' => 'BOLEH LUAR KOTA', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS LUAR KOTA', 'subgrp' => 'STATUS LUAR KOTA', 'text' => 'TIDAK BOLEH LUAR KOTA', 'memo' => 'TIDAK BOLEH LUAR KOTA', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'ZONA TERTENTU', 'subgrp' => 'ZONA TERTENTU', 'text' => 'HARUS ZONA TERTENTU', 'memo' => 'HARUS ZONA TERTENTU', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'ZONA TERTENTU', 'subgrp' => 'ZONA TERTENTU', 'text' => 'TIDAK HARUS KE ZONA TERTENTU', 'memo' => 'TIDAK HARUS KE ZONA TERTENTU', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'BLACKLIST SUPIR', 'subgrp' => 'BLACKLIST SUPIR', 'text' => 'SUPIR BLACKLIST', 'memo' => 'SUPIR BLACKLIST', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'BLACKLIST SUPIR', 'subgrp' => 'BLACKLIST SUPIR', 'text' => 'BUKAN SUPIR BLACKLIST', 'memo' => 'BUKAN SUPIR BLACKLIST', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'KAS GANTUNG', 'subgrp' => 'NOMOR KAS GANTUNG', 'text' => '#KGT# 9999#/#R#/#Y', 'memo' => 'FORMAT NOMOR KAS GANTUNG', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'PROSES ABSENSI', 'subgrp' => 'NOMOR PROSES ABSENSI', 'text' => '#PAB# 9999#/#R#/#Y', 'memo' => 'FORMAT NOMOR PROSES ABSENSI', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'JENIS TRANSAKSI', 'subgrp' => 'JENIS TRANSAKSI', 'text' => 'KAS', 'memo' => 'JENIS TRANSAKSI KAS', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'JENIS TRANSAKSI', 'subgrp' => 'JENIS TRANSAKSI', 'text' => 'BANK', 'memo' => 'JENIS TRANSAKSI BANK', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS LANGSUNG CAIR', 'subgrp' => 'STATUS LANGSUNG CAIR', 'text' => 'LANGSUNG CAIR', 'memo' => 'LANGSUNG CAIR', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS LANGSUNG CAIR', 'subgrp' => 'STATUS LANGSUNG CAIR', 'text' => 'TIDAK LANGSUNG CAIR', 'memo' => 'TIDAK LANGSUNG CAIR', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS DEFAULT', 'subgrp' => 'STATUS DEFAULT', 'text' => 'DEFAULT', 'memo' => 'DEFAULT', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS DEFAULT', 'subgrp' => 'STATUS DEFAULT', 'text' => 'BUKAN DEFAULT', 'memo' => 'BUKAN DEFAULT', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'LUAR KOTA', 'subgrp' => 'LUAR KOTA', 'text' => 'LUAR KOTA', 'memo' => 'LUAR KOTA', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'DALAM KOTA', 'subgrp' => 'DALAM KOTA', 'text' => 'DALAM KOTA', 'memo' => 'DALAM KOTA', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS COA', 'subgrp' => 'STATUS COA', 'text' => 'BANK', 'memo' => 'STATUS COA LINK KE MASTER BANK', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS COA', 'subgrp' => 'STATUS COA', 'text' => 'ALL', 'memo' => 'UNTUK SEMUA', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'SURAT PENGANTAR', 'subgrp' => 'SURAT PENGANTAR', 'text' => '#TRP # 9999#/#R#/#Y', 'memo' => 'SURAT PENGANTAR', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS LONGTRIP', 'subgrp' => 'STATUS LONGTRIP', 'text' => 'LONGTRIP', 'memo' => 'LONGTRIP', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS LONGTRIP', 'subgrp' => 'STATUS LONGTRIP', 'text' => 'BUKAN LONGTRIP', 'memo' => 'BUKAN LONGTRIP', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS PERALIHAN', 'subgrp' => 'STATUS PERALIHAN', 'text' => 'PERALIHAN', 'memo' => 'PERALIHAN', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS PERALIHAN', 'subgrp' => 'STATUS PERALIHAN', 'text' => 'BUKAN PERALIHAN', 'memo' => 'BUKAN PERALIHAN', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS RITASI OMSET', 'subgrp' => 'STATUS RITASI OMSET', 'text' => 'RITASI OMSET', 'memo' => 'RITASI OMSET', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS RITASI OMSET', 'subgrp' => 'STATUS RITASI OMSET', 'text' => 'BUKAN RITASI OMSET', 'memo' => 'BUKAN RITASI OMSET', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS NOTIF', 'subgrp' => 'STATUS NOTIF', 'text' => 'NOTIF', 'memo' => 'NOTIF', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS NOTIF', 'subgrp' => 'STATUS NOTIF', 'text' => 'TIDAK ADA NOTIF', 'memo' => 'TIDAK ADA NOTIF', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS ONE WAY', 'subgrp' => 'STATUS ONE WAY', 'text' => 'ONE WAY', 'memo' => 'ONE WAY', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS ONE WAY', 'subgrp' => 'STATUS ONE WAY', 'text' => 'BUKAN ONE WAY', 'memo' => 'BUKAN ONE WAY', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS EDIT TUJUAN', 'subgrp' => 'STATUS EDIT TUJUAN', 'text' => 'EDIT TUJUAN', 'memo' => 'EDIT TUJUAN', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS EDIT TUJUAN', 'subgrp' => 'STATUS EDIT TUJUAN', 'text' => 'TIDAK BOLEH EDIT TUJUAN', 'memo' => 'TIDAK BOLEH EDIT TUJUAN', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS TRIP', 'subgrp' => 'STATUS TRIP', 'text' => 'ADA TRIP ASAL', 'memo' => 'ADA TRIP ASAL', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS TRIP', 'subgrp' => 'STATUS TRIP', 'text' => 'TIDAK ADA TRIP ASAL', 'memo' => 'TIDAK ADA TRIP ASAL', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS LANGSIR', 'subgrp' => 'STATUS LANGSIR', 'text' => 'LANGSIR', 'memo' => 'LANGSIR', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS LANGSIR', 'subgrp' => 'STATUS LANGSIR', 'text' => 'BUKAN LANGSIR', 'memo' => 'BUKAN LANGSIR', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS BERKAS', 'subgrp' => 'STATUS BERKAS', 'text' => 'ADA BERKAS', 'memo' => 'ADA BERKAS', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS BERKAS', 'subgrp' => 'STATUS BERKAS', 'text' => 'TIDAK ADA BERKAS', 'memo' => 'TIDAK ADA BERKAS', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS POSTING', 'subgrp' => 'STATUS POSTING', 'text' => 'POSTING', 'memo' => 'POSTING', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS POSTING', 'subgrp' => 'STATUS POSTING', 'text' => 'BUKAN POSTING', 'memo' => 'BUKAN POSTING', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS INVOICE', 'subgrp' => 'STATUS INVOICE', 'text' => 'INVOICE UTAMA', 'memo' => 'INVOICE UTAMA', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS INVOICE', 'subgrp' => 'STATUS INVOICE', 'text' => 'INVOICE TAMBAHAN', 'memo' => 'INVOICE TAMBAHAN', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'PENERIMAAN BANK BCA-1', 'subgrp' => 'PENERIMAAN BANK BCA-1', 'text' => '#|BMT-M BCA |# 9999#/#R#/#Y', 'memo' => 'PENERIMAAN BANK BCA-1', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => 'PENERIMAAN BANK',]);
        parameter::create([ 'grp' => 'PENGELUARAN BANK BCA-1', 'subgrp' => 'PENGELUARAN BANK BCA-1', 'text' => '#|BKT-M BCA |# 9999#/#R#/#Y', 'memo' => 'PENGELUARAN BANK BCA-1', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => 'PENGELUARAN BANK',]);
        parameter::create([ 'grp' => 'STATUS RITASI', 'subgrp' => 'STATUS RITASI', 'text' => 'NAIK KEPALA', 'memo' => 'NAIK KEPALA', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS RITASI', 'subgrp' => 'STATUS RITASI', 'text' => 'TURUN KEPALA', 'memo' => 'TURUN KEPALA', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS RITASI', 'subgrp' => 'STATUS RITASI', 'text' => 'PULANG RANGKA', 'memo' => 'PULANG RANGKA', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS RITASI', 'subgrp' => 'STATUS RITASI', 'text' => 'TURUN RANGKA', 'memo' => 'TURUN RANGKA', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS RITASI', 'subgrp' => 'STATUS RITASI', 'text' => 'POTONG GANDENGAN', 'memo' => 'POTONG GANDENGAN', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS BAN', 'subgrp' => 'STATUS BAN', 'text' => 'STOK BAN', 'memo' => 'STOK BAN', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS BAN', 'subgrp' => 'STATUS BAN', 'text' => 'BUKAN STOK BAN', 'memo' => 'BUKAN STOK BAN', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS DAFTAR HARGA', 'subgrp' => 'STATUS DAFTAR HARGA', 'text' => 'BERSEDIA', 'memo' => 'BERSEDIA', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS DAFTAR HARGA', 'subgrp' => 'STATUS DAFTAR HARGA', 'text' => 'TIDAK BERSEDIA', 'memo' => 'TIDAK BERSEDIA', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS TAS', 'subgrp' => 'STATUS TAS', 'text' => 'TAS', 'memo' => 'TAS', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS TAS', 'subgrp' => 'STATUS TAS', 'text' => 'BUKAN TAS', 'memo' => 'BUKAN TAS', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS GUDANG', 'subgrp' => 'STATUS GUDANG', 'text' => 'KANTOR', 'memo' => '', 'type' => '0', 'modifiedby' => '1', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS GUDANG', 'subgrp' => 'STATUS GUDANG', 'text' => 'SEMENTARA', 'memo' => '', 'type' => '0', 'modifiedby' => '1', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS PENYESUAIAN HARGA', 'subgrp' => 'STATUS PENYESUAIAN HARGA', 'text' => 'OK', 'memo' => '', 'type' => '0', 'modifiedby' => '1', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'ORDERANTRUCKING', 'subgrp' => 'ORDERANTRUCKING', 'text' => '9999#/#R#/#Y', 'memo' => '', 'type' => '0', 'modifiedby' => '1', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS LANGSIR', 'subgrp' => 'STATUS LANGSIR', 'text' => 'A', 'memo' => '', 'type' => '0', 'modifiedby' => '1', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS PERALIHAN', 'subgrp' => 'STATUS PERALIHAN', 'text' => 'A', 'memo' => '', 'type' => '0', 'modifiedby' => '1', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'PROSESABSENSISUPIR', 'subgrp' => 'PROSESABSENSISUPIR', 'text' => '#PAS #9999#/#R#/#Y', 'memo' => '', 'type' => '0', 'modifiedby' => '1', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS LONGTRIP', 'subgrp' => 'STATUS LONGTRIP', 'text' => 'YA', 'memo' => '', 'type' => '0', 'modifiedby' => '1', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS PERALIHAN', 'subgrp' => 'STATUS PERALIHAN', 'text' => 'YA', 'memo' => '', 'type' => '0', 'modifiedby' => '1', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS RITASIOMSET', 'subgrp' => 'STATUS RITASIOMSET', 'text' => 'YA', 'memo' => '', 'type' => '0', 'modifiedby' => '1', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'COA KAS GANTUNG', 'subgrp' => 'COA KAS GANTUNG', 'text' => '01.01.02.00', 'memo' => 'COA KAS GANTUNG', 'type' => '0', 'modifiedby' => '1', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS LUAR KOTA', 'subgrp' => 'STATUS LUAR KOTA', 'text' => 'YA', 'memo' => '', 'type' => '0', 'modifiedby' => '1', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS LUAR KOTA', 'subgrp' => 'STATUS LUAR KOTA', 'text' => 'TIDAK', 'memo' => '', 'type' => '0', 'modifiedby' => '1', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'RITASI', 'subgrp' => 'RITASI', 'text' => '#RTT #9999#/#R#/#Y', 'memo' => '', 'type' => '0', 'modifiedby' => '1', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS RITASI', 'subgrp' => 'STATUS RITASI', 'text' => 'YA', 'memo' => '', 'type' => '0', 'modifiedby' => '1', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'JURNAL UMUM BUKTI', 'subgrp' => 'JURNAL UMUM BUKTI', 'text' => '9999#/#R#/#Y#ADJ #', 'memo' => 'JURNAL UMUM', 'type' => '118', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS KAS', 'subgrp' => 'STATUS KAS', 'text' => 'KAS', 'memo' => 'KAS', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS KAS', 'subgrp' => 'STATUS KAS', 'text' => 'BUKAN STATUS KAS', 'memo' => 'BUKAN STATUS KAS', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'RESET PENOMORAN', 'subgrp' => 'RESET PENOMORAN', 'text' => 'RESET BULAN', 'memo' => 'RESET BULAN', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'RESET PENOMORAN', 'subgrp' => 'RESET PENOMORAN', 'text' => 'RESET TAHUN', 'memo' => 'RESET TAHUN', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'COA PIUTANG MANUAL', 'subgrp' => 'DEBET', 'text' => '49', 'memo' => 'COA PIUTANG MANUAL DEBET', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'COA PIUTANG MANUAL', 'subgrp' => 'KREDIT', 'text' => '138', 'memo' => 'COA PIUTANG MANUAL KREDIT', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'PENGELUARAN TRUCKING', 'subgrp' => 'PINJAMAN SUPIR BUKTI', 'text' => '#PJT #9999#/#R#/#Y', 'memo' => 'PINJAMAN SUPIR BUKTI', 'type' => '118', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'PENGELUARAN TRUCKING', 'subgrp' => 'BIAYA LAIN SUPIR BUKTI', 'text' => '#BLS #9999#/#R#/#Y', 'memo' => 'BIAYA LAIN SUPIR BUKTI', 'type' => '118', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'PIUTANG BUKTI', 'subgrp' => 'PIUTANG BUKTI', 'text' => '#EPT #9999#/#R#/#Y', 'memo' => 'PIUTANG BUKTI', 'type' => '118', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'PENERIMAAN TRUCKING', 'subgrp' => 'DEPOSITO SUPIR BUKTI', 'text' => '#DPO #9999#/#R#/#Y', 'memo' => 'PIUTANG BUKTI', 'type' => '118', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'PENERIMAAN TRUCKING', 'subgrp' => 'PENGEMBALIAN PINJAMAN SUPIR BUKTI', 'text' => '#PJP #9999#/#R#/#Y', 'memo' => 'PIUTANG BUKTI', 'type' => '118', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'HUTANG BUKTI', 'subgrp' => 'HUTANG BUKTI', 'text' => '#EHT #9999#/#R#/#Y', 'memo' => 'HUTANG BUKTI', 'type' => '118', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'PELUNASAN PIUTANG BUKTI', 'subgrp' => 'PELUNASAN PIUTANG BUKTI', 'text' => '#PPT #9999#/#R#/#Y', 'memo' => 'PELUNASAN PIUTANG BUKTI', 'type' => '118', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'PEMBAYARAN HUTANG BUKTI', 'subgrp' => 'PEMBAYARAN HUTANG BUKTI', 'text' => '#PHT #9999#/#R#/#Y', 'memo' => 'PEMBAYARAN HUTANG BUKTI', 'type' => '118', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'COA HUTANG MANUAL', 'subgrp' => 'DEBET', 'text' => '88', 'memo' => 'COA HUTANG MANUAL DEBET', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'COA HUTANG MANUAL', 'subgrp' => 'KREDIT', 'text' => '96', 'memo' => 'COA HUTANG MANUAL KREDIT', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'PENERIMAAN STOK', 'subgrp' => 'DELIVERY ORDER BUKTI', 'text' => '#DOT #9999#/#R#/#Y', 'memo' => 'DELIVERY ORDER BUKTI', 'type' => '118', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'PENERIMAAN STOK', 'subgrp' => 'PO STOK BUKTI', 'text' => '#POT #9999#/#R#/#Y', 'memo' => 'PO STOK BUKTI', 'type' => '118', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'PENERIMAAN STOK', 'subgrp' => 'BELI STOK BUKTI', 'text' => '#PBT #9999#/#R#/#Y', 'memo' => 'BELI STOK BUKTI', 'type' => '118', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'PENGELUARAN STOK', 'subgrp' => 'SPK STOK BUKTI', 'text' => '#SPK #9999#/#R#/#Y', 'memo' => 'SPK STOK BUKTI', 'type' => '118', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'PENERIMAAN STOK', 'subgrp' => 'KOREKSI STOK BUKTI', 'text' => '#KST #9999#/#R#/#Y', 'memo' => 'KOREKSI STOK BUKTI', 'type' => '118', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'PENERIMAAN STOK', 'subgrp' => 'PINDAH GUDANG BUKTI', 'text' => '#PGT #9999#/#R#/#Y', 'memo' => 'PINDAH GUDANG BUKTI', 'type' => '118', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'PENERIMAAN STOK', 'subgrp' => 'PERBAIKAN STOK BUKTI', 'text' => '#PST #9999#/#R#/#Y', 'memo' => 'PERBAIKAN STOK BUKTI', 'type' => '118', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'PENGELUARAN STOK', 'subgrp' => 'RETUR BELI BUKTI', 'text' => '#RBT #9999#/#R#/#Y', 'memo' => 'RETUR BELI BUKTI', 'type' => '118', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS HITUNG STOK', 'subgrp' => 'STATUS HITUNG STOK', 'text' => 'YA', 'memo' => 'STATUS HITUNG STOK', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'STATUS HITUNG STOK', 'subgrp' => 'STATUS HITUNG STOK', 'text' => 'TIDAK', 'memo' => 'STATUS HITUNG STOK', 'type' => '0', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'SERVICE IN BUKTI', 'subgrp' => 'SERVICE IN BUKTI', 'text' => '#SIN #9999#/#R#/#Y', 'memo' => 'SERVICE IN BUKTI', 'type' => '118', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'SERVICE OUT BUKTI', 'subgrp' => 'SERVICE OUT BUKTI', 'text' => '#SOU #9999#/#R#/#Y', 'memo' => 'SERVICE OUT BUKTI', 'type' => '118', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'ABSENSI SUPIR APPROVAL BUKTI', 'subgrp' => 'ABSENSI SUPIR APPROVAL BUKTI', 'text' => '#ASA #9999#/#R#/#Y', 'memo' => 'SERVICE OUT BUKTI', 'type' => '118', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'PENERIMAAN STOK', 'subgrp' => 'SALDO STOK BUKTI', 'text' => '#SST #9999#/#R#/#Y', 'memo' => 'SALDO STOK BUKTI', 'type' => '118', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'RINCIAN GAJI SUPIR BUKTI', 'subgrp' => 'RINCIAN GAJI SUPIR BUKTI', 'text' => '#RIC #9999#/#R#/#Y', 'memo' => 'RINCIAN GAJI SUPIR BUKTI', 'type' => '118', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'PENGEMBALIAN KAS GANTUNG BUKTI', 'subgrp' => 'PENGEMBALIAN KAS GANTUNG BUKTI', 'text' => '#PKG #9999#/#R#/#Y', 'memo' => 'PENGEMBALIAN KAS GANTUNG BUKTI', 'type' => '118', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);
        parameter::create([ 'grp' => 'PROSES GAJI SUPIR BUKTI', 'subgrp' => 'PROSES GAJI SUPIR BUKTI', 'text' => '#EBS #9999#/#R#/#Y', 'memo' => 'PROSES GAJI SUPIR BUKTI', 'type' => '118', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);       
        parameter::create([ 'grp' => 'NOTA KREDIT BUKTI', 'subgrp' => 'NOTA KREDIT BUKTI', 'text' => '#EBS #9999#/#R#/#Y', 'memo' => 'NOTA KREDIT BUKTI', 'type' => '118', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);       
        parameter::create([ 'grp' => 'NOTA DEBET BUKTI', 'subgrp' => 'NOTA DEBET BUKTI', 'text' => '#EBS #9999#/#R#/#Y', 'memo' => 'NOTA DEBET BUKTI', 'type' => '118', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);       
        parameter::create([ 'grp' => 'INVOICE BUKTI', 'subgrp' => 'INVOICE BUKTI', 'text' => '#INV #9999#/#R#/#Y', 'memo' => 'INVOICE BUKTI', 'type' => '118', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);       
        parameter::create([ 'grp' => 'INVOICE EXTRA BUKTI', 'subgrp' => 'INVOICE EXTRA BUKTI', 'text' => '#EXT #9999#/#R#/#Y', 'memo' => 'INVOICE EXTRA BUKTI', 'type' => '118', 'modifiedby' => 'ADMIN', 'kelompok' => '',]);       
        parameter::create([ 'grp' => 'COA INVOICE DEBET', 'subgrp' => 'COA INVOICE DEBET', 'text' => '01.03.01.02', 'memo' => 'COA INVOICE DEBET', 'type' => '', 'modifiedby' => 'ADMIN', 'kelompok' => 'COA INVOICE',]);       
        parameter::create([ 'grp' => 'COA INVOICE KREDIT', 'subgrp' => 'COA INVOICE KREDIT', 'text' => '06.01.01.02', 'memo' => 'COA INVOICE KREDIT', 'type' => '', 'modifiedby' => 'ADMIN', 'kelompok' => 'COA INVOICE',]);       
        parameter::create([ 'grp' => 'COA APPROVAL ABSENSI SUPIR KREDIT', 'subgrp' => 'COA APPROVAL ABSENSI SUPIR KREDIT', 'text' => '01.01.01.02', 'memo' => 'COA APPROVAL ABSENSI SUPIR KREDIT', 'type' => '', 'modifiedby' => 'ADMIN', 'kelompok' => 'COA APPROVAL ABSENSI SUPIR',]);       
        parameter::create([ 'grp' => 'COA PEMBAYARAN HUTANG DEBET', 'subgrp' => 'COA PEMBAYARAN HUTANG DEBET', 'text' => '03.02.02.01', 'memo' => 'COA PEMBAYARAN HUTANG DEBET', 'type' => '', 'modifiedby' => 'ADMIN', 'kelompok' => 'COA PEMBAYARAN HUTANG',]);       
        parameter::create([ 'grp' => 'COA APPROVAL ABSENSI SUPIR DEBET', 'subgrp' => 'COA APPROVAL ABSENSI SUPIR DEBET', 'text' => '01.01.02.02', 'memo' => 'COA APPROVAL ABSENSI SUPIR DEBET', 'type' => '', 'modifiedby' => 'ADMIN', 'kelompok' => 'COA APPROVAL ABSENSI SUPIR',]);       


    }
}
