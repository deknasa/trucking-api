<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Method;
use Illuminate\Support\Facades\DB;

class MethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete Method");
        DB::statement("DBCC CHECKIDENT ('Method', RESEED, 1);");

        method::create(['method' => 'absensi', 'keterangan' => 'DATA ABSENSI SUPIR', 'modifiedby' => 'ADMIN', 'info' => '',]);
        method::create(['method' => 'approval', 'keterangan' => 'APPROVAL DATA', 'modifiedby' => 'ADMIN', 'info' => '',]);
        method::create(['method' => 'approvalBatalMuat', 'keterangan' => 'APPROVAL BATAL MUAT', 'modifiedby' => 'ADMIN', 'info' => '',]);
        method::create(['method' => 'approvalBlackListSupir', 'keterangan' => 'APPROVAL BLACKLIST SUPIR', 'modifiedby' => 'ADMIN', 'info' => '',]);
        method::create(['method' => 'approvaledit', 'keterangan' => 'APPROVAL EDIT', 'modifiedby' => 'ADMIN', 'info' => '',]);
        method::create(['method' => 'approvalEditAbsensi', 'keterangan' => 'APPROVAL EDIT ABSENSI', 'modifiedby' => 'ADMIN', 'info' => '',]);
        method::create(['method' => 'approvalEditTujuan', 'keterangan' => 'APPROVAL EDIT TUJUAN', 'modifiedby' => 'ADMIN', 'info' => '',]);
        method::create(['method' => 'approvalSupirLuarKota', 'keterangan' => 'APPROVAL SUPIR LUAR KOTA', 'modifiedby' => 'ADMIN', 'info' => '',]);
        method::create(['method' => 'approvalSupirResign', 'keterangan' => 'APPROVAL SUPIR RESIGN', 'modifiedby' => 'ADMIN', 'info' => '',]);
        method::create(['method' => 'cekabsensi', 'keterangan' => 'CEK ABSENSI', 'modifiedby' => 'ADMIN', 'info' => '',]);
        method::create(['method' => 'copy', 'keterangan' => 'COPY DATA', 'modifiedby' => 'ADMIN', 'info' => '',]);
        method::create(['method' => 'deposito', 'keterangan' => 'DATA DEPOSITO', 'modifiedby' => 'ADMIN', 'info' => '',]);
        method::create(['method' => 'destroy', 'keterangan' => 'HAPUS DATA', 'modifiedby' => 'ADMIN', 'info' => '',]);
        method::create(['method' => 'detail', 'keterangan' => 'DETAIL DATA', 'modifiedby' => 'ADMIN', 'info' => '',]);
        method::create(['method' => 'export', 'keterangan' => 'EXPORT DATA', 'modifiedby' => 'ADMIN', 'info' => '',]);
        method::create(['method' => 'get', 'keterangan' => 'DAPATKAN DATA', 'modifiedby' => 'ADMIN', 'info' => '',]);
        method::create(['method' => 'getJurnal', 'keterangan' => 'DAPATKAN DATA JURNAL', 'modifiedby' => 'ADMIN', 'info' => '',]);
        method::create(['method' => 'getPelunasan', 'keterangan' => 'DAPATKAN DAFTAR PELUNASAN', 'modifiedby' => 'ADMIN', 'info' => '',]);
        method::create(['method' => 'getPenerimaan', 'keterangan' => 'DAPATKAN DATA PENERIMAAN', 'modifiedby' => 'ADMIN', 'info' => '',]);
        method::create(['method' => 'getPengeluaran', 'keterangan' => 'DAPATKAN DAFTAR PELUNASAN', 'modifiedby' => 'ADMIN', 'info' => '',]);
        method::create(['method' => 'header', 'keterangan' => 'DATA HEADER', 'modifiedby' => 'ADMIN', 'info' => '',]);
        method::create(['method' => 'import', 'keterangan' => 'IMPORT DATA', 'modifiedby' => 'ADMIN', 'info' => '',]);
        method::create(['method' => 'index', 'keterangan' => 'TAMPILAN DATA', 'modifiedby' => 'ADMIN', 'info' => '',]);
        method::create(['method' => 'isTanggalAvaillable', 'keterangan' => 'DATA TANGGAL TIDAK TERSEDIA', 'modifiedby' => 'ADMIN', 'info' => '',]);
        method::create(['method' => 'jurnalBBM', 'keterangan' => 'DATA JURNAL BBM', 'modifiedby' => 'ADMIN', 'info' => '',]);
        method::create(['method' => 'listpivot', 'keterangan' => 'DATA LIST PIVOT', 'modifiedby' => 'ADMIN', 'info' => '',]);
        method::create(['method' => 'potPribadi', 'keterangan' => 'DATA POTONGAN PRIBADI', 'modifiedby' => 'ADMIN', 'info' => '',]);
        method::create(['method' => 'potSemua', 'keterangan' => 'DATA POTONGANS EMUA', 'modifiedby' => 'ADMIN', 'info' => '',]);
        method::create(['method' => 'report', 'keterangan' => 'REPORT', 'modifiedby' => 'ADMIN', 'info' => '',]);
        method::create(['method' => 'resequence', 'keterangan' => 'PENGURUTAN MENU', 'modifiedby' => 'ADMIN', 'info' => '',]);
        method::create(['method' => 'show', 'keterangan' => 'TAMPILAN DATA', 'modifiedby' => 'ADMIN', 'info' => '',]);
        method::create(['method' => 'store', 'keterangan' => 'SIMPAN DATA', 'modifiedby' => 'ADMIN', 'info' => '',]);
        method::create(['method' => 'storeResequence', 'keterangan' => 'SIMPAN PENGURUTAN MENU', 'modifiedby' => 'ADMIN', 'info' => '',]);
        method::create(['method' => 'transfer', 'keterangan' => 'TRANSFER DATA', 'modifiedby' => 'ADMIN', 'info' => '',]);
        method::create(['method' => 'update', 'keterangan' => 'UPDATE DATA', 'modifiedby' => 'ADMIN', 'info' => '',]);
        method::create(['method' => 'updateTanggalBatas', 'keterangan' => 'UPDATE TGL BATAS', 'modifiedby' => 'ADMIN', 'info' => '',]);
    }
}
