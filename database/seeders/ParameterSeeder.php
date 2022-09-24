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

        Parameter::create(['grp' => 'STATUS AKTIF',  'subgrp' => 'STATUS AKTIF',  'text' => 'AKTIF',  'type' => '0',  'memo' => 'UNTUK STATUS AKTIF',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS AKTIF',  'subgrp' => 'STATUS AKTIF',  'text' => 'NON AKTIF',  'type' => '0',  'memo' => 'UNTUK STATUS NON AKTIF',  'modifiedby' => 'admin',]);
        Parameter::create(['grp' => 'STATUS APPROVAL',  'subgrp' => 'STATUS APPROVAL',  'text' => 'APPROVAL',  'type' => '0',  'memo' => 'APPROVAL',  'modifiedby' => 'admin',]);
        Parameter::create(['grp' => 'STATUS APPROVAL',  'subgrp' => 'STATUS APPROVAL',  'text' => 'NON APPROVAL',  'type' => '0',  'memo' => 'NON APPROVAL',  'modifiedby' => 'admin',]);
        Parameter::create(['grp' => 'ABSENSI',  'subgrp' => 'ABSENSI',  'text' => '#ABS # 9999#/#R#/#Y',  'type' => '0',  'memo' => 'UNTUK PENOMORAN ABSENSI',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS KARYAWAN',  'subgrp' => 'STATUS KARYAWAN',  'text' => 'KARYAWAN',  'type' => '0',  'memo' => 'KARYAWAN',  'modifiedby' => 'admin',]);
        Parameter::create(['grp' => 'STATUS KARYAWAN',  'subgrp' => 'STATUS KARYAWAN',  'text' => 'NON KARYAWAN',  'type' => '0',  'memo' => 'NON KARYAWAN',  'modifiedby' => 'admin',]);
        Parameter::create(['grp' => 'COA',  'subgrp' => 'AP',  'text' => 'AP',  'type' => '0',  'memo' => 'AP',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'COA',  'subgrp' => 'AP',  'text' => 'NON AP',  'type' => '0',  'memo' => 'NON AP',  'modifiedby' => 'admin',]);
        Parameter::create(['grp' => 'COA',  'subgrp' => 'NERACA',  'text' => 'NERACA',  'type' => '0',  'memo' => 'NERACA',  'modifiedby' => 'admin',]);
        Parameter::create(['grp' => 'COA',  'subgrp' => 'NERACA',  'text' => 'NON NERACA',  'type' => '0',  'memo' => 'NON NERACA',  'modifiedby' => 'admin',]);
        Parameter::create(['grp' => 'COA',  'subgrp' => 'LABA RUGI',  'text' => 'LABA RUGI',  'type' => '0',  'memo' => 'LABA RUGI',  'modifiedby' => 'admin',]);
        Parameter::create(['grp' => 'COA',  'subgrp' => 'LABA RUGI',  'text' => 'NON LABA RUGI',  'type' => '0',  'memo' => 'NON LABA RUGI',  'modifiedby' => 'admin',]);
        Parameter::create(['grp' => 'STATUS KENDARAAN',  'subgrp' => 'STATUS KENDARAAN',  'text' => 'KENDARAAN',  'type' => '0',  'memo' => 'KENDARAAN',  'modifiedby' => 'admin',]);
        Parameter::create(['grp' => 'STATUS KENDARAAN',  'subgrp' => 'STATUS KENDARAAN',  'text' => 'NON KENDARAAN',  'type' => '0',  'memo' => 'NON KENDARAAN',  'modifiedby' => 'admin',]);
        Parameter::create(['grp' => 'STATUS STANDARISASI',  'subgrp' => 'STATUS STANDARISASI',  'text' => 'STANDARISASI',  'type' => '0',  'memo' => 'STANDARISASI',  'modifiedby' => 'admin',]);
        Parameter::create(['grp' => 'STATUS STANDARISASI',  'subgrp' => 'STATUS STANDARISASI',  'text' => 'NON STANDARISASI',  'type' => '0',  'memo' => 'NON STANDARISASI',  'modifiedby' => 'admin',]);
        Parameter::create(['grp' => 'JENIS GANDENGAN',  'subgrp' => 'JENIS GANDENGAN',  'text' => '20 FEET',  'type' => '0',  'memo' => '20 FEET',  'modifiedby' => 'admin',]);
        Parameter::create(['grp' => 'JENIS GANDENGAN',  'subgrp' => 'JENIS GANDENGAN',  'text' => '40 FEET',  'type' => '0',  'memo' => '40 FEET',  'modifiedby' => 'admin',]);
        Parameter::create(['grp' => 'JENIS PLAT',  'subgrp' => 'JENIS PLAT',  'text' => 'PLAT KUNING',  'type' => '0',  'memo' => 'PLAT KUNING',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'JENIS PLAT',  'subgrp' => 'JENIS PLAT',  'text' => 'PLAT HITAM',  'type' => '0',  'memo' => 'PLAT HITAM',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS MUTASI',  'subgrp' => 'STATUS MUTASI',  'text' => 'MUTASI',  'type' => '0',  'memo' => 'MUTASI',  'modifiedby' => 'admin',]);
        Parameter::create(['grp' => 'STATUS MUTASI',  'subgrp' => 'STATUS MUTASI',  'text' => 'NON MUTASI',  'type' => '0',  'memo' => 'NON MUTASI',  'modifiedby' => 'admin',]);
        Parameter::create(['grp' => 'STATUS VALIDASI KENDARAAN',  'subgrp' => 'STATUS VALIDASI KENDARAAN',  'text' => 'VALIDASI KENDARAAN',  'type' => '0',  'memo' => 'VALIDASI KENDARAAN',  'modifiedby' => 'admin',]);
        Parameter::create(['grp' => 'STATUS VALIDASI KENDARAAN',  'subgrp' => 'STATUS VALIDASI KENDARAAN',  'text' => 'NON VALIDASI KENDARAAN',  'type' => '0',  'memo' => 'NON VALIDASI KENDARAAN',  'modifiedby' => 'admin',]);
        Parameter::create(['grp' => 'STATUS MOBIL STORING',  'subgrp' => 'STATUS MOBIL STORING',  'text' => 'MOBIL STORING',  'type' => '0',  'memo' => 'MOBIL STORING',  'modifiedby' => 'admin',]);
        Parameter::create(['grp' => 'STATUS MOBIL STORING',  'subgrp' => 'STATUS MOBIL STORING',  'text' => 'NON MOBIL STORING',  'type' => '0',  'memo' => 'NON MOBIL STORING',  'modifiedby' => 'admin',]);
        Parameter::create(['grp' => 'STATUS APPROVAL EDIT BAN',  'subgrp' => 'STATUS APPROVAL EDIT BAN',  'text' => 'APPROVAL EDIT BAN',  'type' => '0',  'memo' => 'APPROVAL EDIT BAN',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS APPROVAL EDIT BAN',  'subgrp' => 'STATUS APPROVAL EDIT BAN',  'text' => 'UNAPPROVAL EDIT BAN',  'type' => '0',  'memo' => 'UNAPPROVAL EDIT BAN',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS LEWAT VALIDASI',  'subgrp' => 'STATUS LEWAT VALIDASI',  'text' => 'APPROVAL LEWAT VALIDASI',  'type' => '0',  'memo' => 'APPROVAL LEWAT VALIDASI',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS LEWAT VALIDASI',  'subgrp' => 'STATUS LEWAT VALIDASI',  'text' => 'UNAPPROVAL LEWAT VALIDASI',  'type' => '0',  'memo' => 'UNAPPROVAL LEWAT VALIDASI',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'PENERIMAAN KAS',  'subgrp' => 'NOMOR PENERIMAAN KAS',  'text' => '#|KMT |# 9999#/#R#/#Y',  'type' => '0',  'memo' => 'NOMOR PENERIMAAN KAS',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'PENGELUARAN KAS',  'subgrp' => 'NOMOR  PENGELUARAN KAS',  'text' => '#|KBT |# 9999#/#R#/#Y',  'type' => '0',  'memo' => 'NOMOR PENGELUARAN KAS',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS ACCOUNT PAYABLE',  'subgrp' => 'STATUS ACCOUNT PAYABLE',  'text' => 'STATUS AKTIF ACCOUNT PAYABLE',  'type' => '0',  'memo' => 'STATUS AKTIF ACCOUNT PAYABLE',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS ACCOUNT PAYABLE',  'subgrp' => 'STATUS ACCOUNT PAYABLE',  'text' => 'STATUS NON AKTIF ACCOUNT PAYABLE',  'type' => '0',  'memo' => 'STATUS NON AKTIF ACCOUNT PAYABLE',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS NERACA',  'subgrp' => 'STATUS NERACA',  'text' => 'STATUS AKTIF NERACA',  'type' => '0',  'memo' => 'STATUS AKTIF NERACA',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS NERACA',  'subgrp' => 'STATUS NERACA',  'text' => 'STATUS NON AKTIF NERACA',  'type' => '0',  'memo' => 'STATUS NON AKTIF NERACA',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS LABA RUGI',  'subgrp' => 'STATUS LABA RUGI',  'text' => 'STATUS AKTIF LABA RUGI',  'type' => '0',  'memo' => 'STATUS AKTIF LABA RUGI',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS LABA RUGI',  'subgrp' => 'STATUS LABA RUGI',  'text' => 'STATUS NON AKTIF LABA RUGI',  'type' => '0',  'memo' => 'STATUS NON AKTIF LABA RUGI',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'SISTEM TON',  'subgrp' => 'SISTEM TON',  'text' => 'AKTIF SISTEM TON',  'type' => '0',  'memo' => 'AKTIF SISTEM TON',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'SISTEM TON',  'subgrp' => 'SISTEM TON',  'text' => 'NON AKTIF SISTEM TON',  'type' => '0',  'memo' => 'NON AKTIF SISTEM TON',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'PENYESUAIAN HARGA',  'subgrp' => 'PENYESUAIAN HARGA',  'text' => 'PENYESUAIAN HARGA',  'type' => '0',  'memo' => 'PENYESUAIAN HARGA',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'PENYESUAIAN HARGA',  'subgrp' => 'PENYESUAIAN HARGA',  'text' => 'BUKAN PENYESUAIAN HARGA',  'type' => '0',  'memo' => 'BUKAN PENYESUAIAN HARGA',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS ADA UPDATE GAMBAR',  'subgrp' => 'STATUS ADA UPDATE GAMBAR',  'text' => 'WAJIB ADA UPDATE GAMBAR',  'type' => '0',  'memo' => 'WAJIB ADA UPDATE GAMBAR',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS ADA UPDATE GAMBAR',  'subgrp' => 'STATUS ADA UPDATE GAMBAR',  'text' => 'TIDAK WAJIB ADA UPDATE GAMBAR',  'type' => '0',  'memo' => 'TIDAK WAJIB ADA UPDATE GAMBAR',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS LUAR KOTA',  'subgrp' => 'STATUS LUAR KOTA',  'text' => 'BOLEH LUAR KOTA',  'type' => '0',  'memo' => 'BOLEH LUAR KOTA',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS LUAR KOTA',  'subgrp' => 'STATUS LUAR KOTA',  'text' => 'TIDAK BOLEH LUAR KOTA',  'type' => '0',  'memo' => 'TIDAK BOLEH LUAR KOTA',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'ZONA TERTENTU',  'subgrp' => 'ZONA TERTENTU',  'text' => 'HARUS ZONA TERTENTU',  'type' => '0',  'memo' => 'HARUS ZONA TERTENTU',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'ZONA TERTENTU',  'subgrp' => 'ZONA TERTENTU',  'text' => 'TIDAK HARUS KE ZONA TERTENTU',  'type' => '0',  'memo' => 'TIDAK HARUS KE ZONA TERTENTU',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'BLACKLIST SUPIR',  'subgrp' => 'BLACKLIST SUPIR',  'text' => 'SUPIR BLACKLIST',  'type' => '0',  'memo' => 'SUPIR BLACKLIST',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'BLACKLIST SUPIR',  'subgrp' => 'BLACKLIST SUPIR',  'text' => 'BUKAN SUPIR BLACKLIST',  'type' => '0',  'memo' => 'BUKAN SUPIR BLACKLIST',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'KAS GANTUNG',  'subgrp' => 'NOMOR KAS GANTUNG',  'text' => '#KGT# 9999#/#R#/#Y',  'type' => '0',  'memo' => 'FORMAT NOMOR KAS GANTUNG',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'PROSES ABSENSI',  'subgrp' => 'NOMOR PROSES ABSENSI',  'text' => '#PAB# 9999#/#R#/#Y',  'type' => '0',  'memo' => 'FORMAT NOMOR PROSES ABSENSI',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'JENIS TRANSAKSI',  'subgrp' => 'JENIS TRANSAKSI',  'text' => 'KAS',  'type' => '0',  'memo' => 'JENIS TRANSAKSI KAS',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'JENIS TRANSAKSI',  'subgrp' => 'JENIS TRANSAKSI',  'text' => 'BANK',  'type' => '0',  'memo' => 'JENIS TRANSAKSI BANK',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS LANGSUNG CAIR',  'subgrp' => 'STATUS LANGSUNG CAIR',  'text' => 'LANGSUNG CAIR',  'type' => '0',  'memo' => 'LANGSUNG CAIR',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS LANGSUNG CAIR',  'subgrp' => 'STATUS LANGSUNG CAIR',  'text' => 'TIDAK LANGSUNG CAIR',  'type' => '0',  'memo' => 'TIDAK LANGSUNG CAIR',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS DEFAULT',  'subgrp' => 'STATUS DEFAULT',  'text' => 'DEFAULT',  'type' => '0',  'memo' => 'DEFAULT',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS DEFAULT',  'subgrp' => 'STATUS DEFAULT',  'text' => 'BUKAN DEFAULT',  'type' => '0',  'memo' => 'BUKAN DEFAULT',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'LUAR KOTA',  'subgrp' => 'LUAR KOTA',  'text' => 'LUAR KOTA',  'type' => '0',  'memo' => 'LUAR KOTA',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'DALAM KOTA',  'subgrp' => 'DALAM KOTA',  'text' => 'DALAM KOTA',  'type' => '0',  'memo' => 'DALAM KOTA',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS COA',  'subgrp' => 'STATUS COA',  'text' => 'BANK',  'type' => '0',  'memo' => 'STATUS COA LINK KE MASTER BANK',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS COA',  'subgrp' => 'STATUS COA',  'text' => 'ALL',  'type' => '0',  'memo' => 'UNTUK SEMUA',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'SURAT PENGANTAR',  'subgrp' => 'SURAT PENGANTAR',  'text' => '#TRP # 9999#/#R#/#Y',  'type' => '0',  'memo' => 'SURAT PENGANTAR',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS LONGTRIP',  'subgrp' => 'STATUS LONGTRIP',  'text' => 'LONGTRIP',  'type' => '0',  'memo' => 'LONGTRIP',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS LONGTRIP',  'subgrp' => 'STATUS LONGTRIP',  'text' => 'BUKAN LONGTRIP',  'type' => '0',  'memo' => 'BUKAN LONGTRIP',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS PERALIHAN',  'subgrp' => 'STATUS PERALIHAN',  'text' => 'PERALIHAN',  'type' => '0',  'memo' => 'PERALIHAN',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS PERALIHAN',  'subgrp' => 'STATUS PERALIHAN',  'text' => 'BUKAN PERALIHAN',  'type' => '0',  'memo' => 'BUKAN PERALIHAN',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS RITASI OMSET',  'subgrp' => 'STATUS RITASI OMSET',  'text' => 'RITASI OMSET',  'type' => '0',  'memo' => 'RITASI OMSET',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS RITASI OMSET',  'subgrp' => 'STATUS RITASI OMSET',  'text' => 'BUKAN RITASI OMSET',  'type' => '0',  'memo' => 'BUKAN RITASI OMSET',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS NOTIF',  'subgrp' => 'STATUS NOTIF',  'text' => 'NOTIF',  'type' => '0',  'memo' => 'NOTIF',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS NOTIF',  'subgrp' => 'STATUS NOTIF',  'text' => 'TIDAK ADA NOTIF',  'type' => '0',  'memo' => 'TIDAK ADA NOTIF',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS ONE WAY',  'subgrp' => 'STATUS ONE WAY',  'text' => 'ONE WAY',  'type' => '0',  'memo' => 'ONE WAY',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS ONE WAY',  'subgrp' => 'STATUS ONE WAY',  'text' => 'BUKAN ONE WAY',  'type' => '0',  'memo' => 'BUKAN ONE WAY',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS EDIT TUJUAN',  'subgrp' => 'STATUS EDIT TUJUAN',  'text' => 'EDIT TUJUAN',  'type' => '0',  'memo' => 'EDIT TUJUAN',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS EDIT TUJUAN',  'subgrp' => 'STATUS EDIT TUJUAN',  'text' => 'TIDAK BOLEH EDIT TUJUAN',  'type' => '0',  'memo' => 'TIDAK BOLEH EDIT TUJUAN',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS TRIP',  'subgrp' => 'STATUS TRIP',  'text' => 'ADA TRIP ASAL',  'type' => '0',  'memo' => 'ADA TRIP ASAL',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS TRIP',  'subgrp' => 'STATUS TRIP',  'text' => 'TIDAK ADA TRIP ASAL',  'type' => '0',  'memo' => 'TIDAK ADA TRIP ASAL',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS LANGSIR',  'subgrp' => 'STATUS LANGSIR',  'text' => 'LANGSIR',  'type' => '0',  'memo' => 'LANGSIR',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS LANGSIR',  'subgrp' => 'STATUS LANGSIR',  'text' => 'BUKAN LANGSIR',  'type' => '0',  'memo' => 'BUKAN LANGSIR',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS BERKAS',  'subgrp' => 'STATUS BERKAS',  'text' => 'ADA BERKAS',  'type' => '0',  'memo' => 'ADA BERKAS',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS BERKAS',  'subgrp' => 'STATUS BERKAS',  'text' => 'TIDAK ADA BERKAS',  'type' => '0',  'memo' => 'TIDAK ADA BERKAS',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS POSTING',  'subgrp' => 'STATUS POSTING',  'text' => 'POSTING',  'type' => '0',  'memo' => 'POSTING',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS POSTING',  'subgrp' => 'STATUS POSTING',  'text' => 'BUKAN POSTING',  'type' => '0',  'memo' => 'BUKAN POSTING',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS INVOICE',  'subgrp' => 'STATUS INVOICE',  'text' => 'INVOICE UTAMA',  'type' => '0',  'memo' => 'INVOICE UTAMA',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS INVOICE',  'subgrp' => 'STATUS INVOICE',  'text' => 'INVOICE TAMBAHAN',  'type' => '0',  'memo' => 'INVOICE TAMBAHAN',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'PENERIMAAN BANK BCA-1',  'subgrp' => 'PENERIMAAN BANK BCA-1',  'text' => '#|BMT-M BCA |# 9999#/#R#/#Y',  'type' => '0',  'memo' => 'PENERIMAAN BANK BCA-1',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'PENGELUARAN BANK BCA-1',  'subgrp' => 'PENGELUARAN BANK BCA-1',  'text' => '#|BKT-M BCA |# 9999#/#R#/#Y',  'type' => '0',  'memo' => 'PENGELUARAN BANK BCA-1',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS RITASI',  'subgrp' => 'STATUS RITASI',  'text' => 'NAIK KEPALA',  'type' => '0',  'memo' => 'NAIK KEPALA',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS RITASI',  'subgrp' => 'STATUS RITASI',  'text' => 'TURUN KEPALA',  'type' => '0',  'memo' => 'TURUN KEPALA',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS RITASI',  'subgrp' => 'STATUS RITASI',  'text' => 'PULANG RANGKA',  'type' => '0',  'memo' => 'PULANG RANGKA',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS RITASI',  'subgrp' => 'STATUS RITASI',  'text' => 'TURUN RANGKA',  'type' => '0',  'memo' => 'TURUN RANGKA',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS RITASI',  'subgrp' => 'STATUS RITASI',  'text' => 'POTONG GANDENGAN',  'type' => '0',  'memo' => 'POTONG GANDENGAN',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS BAN',  'subgrp' => 'STATUS BAN',  'text' => 'STOK BAN',  'type' => '0',  'memo' => 'STOK BAN',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS BAN',  'subgrp' => 'STATUS BAN',  'text' => 'BUKAN STOK BAN',  'type' => '0',  'memo' => 'BUKAN STOK BAN',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS DAFTAR HARGA',  'subgrp' => 'STATUS DAFTAR HARGA',  'text' => 'BERSEDIA',  'type' => '0',  'memo' => 'BERSEDIA',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS DAFTAR HARGA',  'subgrp' => 'STATUS DAFTAR HARGA',  'text' => 'TIDAK BERSEDIA',  'type' => '0',  'memo' => 'TIDAK BERSEDIA',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS TAS',  'subgrp' => 'STATUS TAS',  'text' => 'TAS',  'type' => '0',  'memo' => 'TAS',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS TAS',  'subgrp' => 'STATUS TAS',  'text' => 'BUKAN TAS',  'type' => '0',  'memo' => 'BUKAN TAS',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS GUDANG',  'subgrp' => 'STATUS GUDANG',  'text' => 'KANTOR',  'type' => '0',  'memo' => '',  'modifiedby' => '1',]);
        Parameter::create(['grp' => 'STATUS GUDANG',  'subgrp' => 'STATUS GUDANG',  'text' => 'SEMENTARA',  'type' => '0',  'memo' => '',  'modifiedby' => '1',]);
        Parameter::create(['grp' => 'STATUS PENYESUAIAN HARGA',  'subgrp' => 'STATUS PENYESUAIAN HARGA',  'text' => 'OK',  'type' => '0',  'memo' => '',  'modifiedby' => '1',]);
        Parameter::create(['grp' => 'ORDERANTRUCKING',  'subgrp' => 'ORDERANTRUCKING',  'text' => '#JOB #9999#/#R#/#Y',  'type' => '0',  'memo' => '',  'modifiedby' => '1',]);
        Parameter::create(['grp' => 'STATUS LANGSIR',  'subgrp' => 'STATUS LANGSIR',  'text' => 'A',  'type' => '0',  'memo' => '',  'modifiedby' => '1',]);
        Parameter::create(['grp' => 'STATUS PERALIHAN',  'subgrp' => 'STATUS PERALIHAN',  'text' => 'A',  'type' => '0',  'memo' => '',  'modifiedby' => '1',]);
        Parameter::create(['grp' => 'PROSESABSENSISUPIR',  'subgrp' => 'PROSESABSENSISUPIR',  'text' => '#PAS #9999#/#R#/#Y',  'type' => '0',  'memo' => '',  'modifiedby' => '1',]);
        Parameter::create(['grp' => 'STATUS LONGTRIP',  'subgrp' => 'STATUS LONGTRIP',  'text' => 'YA',  'type' => '0',  'memo' => '',  'modifiedby' => '1',]);
        Parameter::create(['grp' => 'STATUS PERALIHAN',  'subgrp' => 'STATUS PERALIHAN',  'text' => 'YA',  'type' => '0',  'memo' => '',  'modifiedby' => '1',]);
        Parameter::create(['grp' => 'STATUS RITASIOMSET',  'subgrp' => 'STATUS RITASIOMSET',  'text' => 'YA',  'type' => '0',  'memo' => '',  'modifiedby' => '1',]);
        Parameter::create(['grp' => 'SURATPENGANTAR',  'subgrp' => 'SURATPENGANTAR',  'text' => '#TRP #9999#/#R#/#Y',  'type' => '0',  'memo' => '',  'modifiedby' => '1',]);
        Parameter::create(['grp' => 'STATUS LUAR KOTA',  'subgrp' => 'STATUS LUAR KOTA',  'text' => 'YA',  'type' => '0',  'memo' => '',  'modifiedby' => '1',]);
        Parameter::create(['grp' => 'STATUS LUAR KOTA',  'subgrp' => 'STATUS LUAR KOTA',  'text' => 'TIDAK',  'type' => '0',  'memo' => '',  'modifiedby' => '1',]);
        Parameter::create(['grp' => 'RITASI',  'subgrp' => 'RITASI',  'text' => '#RTT #9999#/#R#/#Y',  'type' => '0',  'memo' => '',  'modifiedby' => '1',]);
        Parameter::create(['grp' => 'STATUS RITASI',  'subgrp' => 'STATUS RITASI',  'text' => 'YA',  'type' => '0',  'memo' => '',  'modifiedby' => '1',]);
        Parameter::create(['grp' => 'JURNAL UMUM BUKTI',  'subgrp' => 'JURNAL UMUM BUKTI',  'text' => '9999#/#R#/#Y#ADJ #',  'type' => '118',  'memo' => 'JURNAL UMUM',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS KAS',  'subgrp' => 'STATUS KAS',  'text' => 'KAS',  'type' => '0',  'memo' => 'KAS',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS KAS',  'subgrp' => 'STATUS KAS',  'text' => 'BUKAN STATUS KAS',  'type' => '0',  'memo' => 'BUKAN STATUS KAS',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'RESET PENOMORAN',  'subgrp' => 'RESET PENOMORAN',  'text' => 'RESET BULAN',  'type' => '0',  'memo' => 'RESET BULAN',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'RESET PENOMORAN',  'subgrp' => 'RESET PENOMORAN',  'text' => 'RESET TAHUN',  'type' => '0',  'memo' => 'RESET TAHUN',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'COA PIUTANG MANUAL',  'subgrp' => 'DEBET',  'text' => '49',  'type' => '0',  'memo' => 'COA PIUTANG MANUAL DEBET',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'COA PIUTANG MANUAL',  'subgrp' => 'KREDIT',  'text' => '138',  'type' => '0',  'memo' => 'COA PIUTANG MANUAL KREDIT',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'PINJAMAN SUPIR BUKTI',  'subgrp' => 'PINJAMAN SUPIR BUKTI',  'text' => '#PJT #9999#/#R#/#Y',  'type' => '118',  'memo' => 'PINJAMAN SUPIR BUKTI',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'BIAYA LAIN SUPIR BUKTI',  'subgrp' => 'BIAYA LAIN SUPIR BUKTI',  'text' => '#BLS #9999#/#R#/#Y',  'type' => '118',  'memo' => 'BIAYA LAIN SUPIR BUKTI',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'PIUTANG BUKTI',  'subgrp' => 'PIUTANG BUKTI',  'text' => '#EPT #9999#/#R#/#Y',  'type' => '118',  'memo' => 'PIUTANG BUKTI',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'DEPOSITO SUPIR BUKTI',  'subgrp' => 'DEPOSITO SUPIR BUKTI',  'text' => '#DPO #9999#/#R#/#Y',  'type' => '118',  'memo' => 'PIUTANG BUKTI',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'PENGEMBALIAN PINJAMAN SUPIR BUKTI',  'subgrp' => 'PENGEMBALIAN PINJAMAN SUPIR BUKTI',  'text' => '#PJP #9999#/#R#/#Y',  'type' => '118',  'memo' => 'PIUTANG BUKTI',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'HUTANG BUKTI',  'subgrp' => 'HUTANG BUKTI',  'text' => '#EHT #9999#/#R#/#Y',  'type' => '118',  'memo' => 'HUTANG BUKTI',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'PELUNASAN PIUTANG BUKTI',  'subgrp' => 'PELUNASAN PIUTANG BUKTI',  'text' => '#PPT #9999#/#R#/#Y',  'type' => '118',  'memo' => 'PELUNASAN PIUTANG BUKTI',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'PEMBAYARAN HUTANG BUKTI',  'subgrp' => 'PEMBAYARAN HUTANG BUKTI',  'text' => '#PHT #9999#/#R#/#Y',  'type' => '118',  'memo' => 'PEMBAYARAN HUTANG BUKTI',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'COA HUTANG MANUAL',  'subgrp' => 'DEBET',  'text' => '88',  'type' => '0',  'memo' => 'COA HUTANG MANUAL DEBET',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'COA HUTANG MANUAL',  'subgrp' => 'KREDIT',  'text' => '96',  'type' => '0',  'memo' => 'COA HUTANG MANUAL KREDIT',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'DELIVERY ORDER BUKTI',  'subgrp' => 'DELIVERY ORDER BUKTI',  'text' => '#DOT #9999#/#R#/#Y',  'type' => '118',  'memo' => 'DELIVERY ORDER BUKTI',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'PO STOK BUKTI',  'subgrp' => 'PO STOK BUKTI',  'text' => '#POT #9999#/#R#/#Y',  'type' => '118',  'memo' => 'PO STOK BUKTI',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'BELI STOK BUKTI',  'subgrp' => 'BELI STOK BUKTI',  'text' => '#PBT #9999#/#R#/#Y',  'type' => '118',  'memo' => 'BELI STOK BUKTI',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'SPK STOK BUKTI',  'subgrp' => 'SPK STOK BUKTI',  'text' => '#SPK #9999#/#R#/#Y',  'type' => '118',  'memo' => 'SPK STOK BUKTI',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'KOREKSI STOK BUKTI',  'subgrp' => 'KOREKSI STOK BUKTI',  'text' => '#KST #9999#/#R#/#Y',  'type' => '118',  'memo' => 'KOREKSI STOK BUKTI',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'PINDAH GUDANG BUKTI',  'subgrp' => 'PINDAH GUDANG BUKTI',  'text' => '#PGT #9999#/#R#/#Y',  'type' => '118',  'memo' => 'PINDAH GUDANG BUKTI',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'PERBAIKAN STOK BUKTI',  'subgrp' => 'PERBAIKAN STOK BUKTI',  'text' => '#PST #9999#/#R#/#Y',  'type' => '118',  'memo' => 'PERBAIKAN STOK BUKTI',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'RETUR BELI BUKTI',  'subgrp' => 'RETUR BELI BUKTI',  'text' => '#RBT #9999#/#R#/#Y',  'type' => '118',  'memo' => 'RETUR BELI BUKTI',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS HITUNG STOK',  'subgrp' => 'STATUS HITUNG STOK',  'text' => 'YA',  'type' => '0',  'memo' => 'STATUS HITUNG STOK',  'modifiedby' => 'ADMIN',]);
        Parameter::create(['grp' => 'STATUS HITUNG STOK',  'subgrp' => 'STATUS HITUNG STOK',  'text' => 'TIDAK',  'type' => '0',  'memo' => 'STATUS HITUNG STOK',  'modifiedby' => 'ADMIN',]);
       


    }
}
