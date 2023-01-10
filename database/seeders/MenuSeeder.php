<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete Menu");
        DB::statement("DBCC CHECKIDENT ('Menu', RESEED, 1);");

        Menu::create(['menuname' => 'DASHBOARD', 'menuseq' => '0', 'menuparent' => '0', 'menuicon' => 'FAS FA-HOME', 'aco_id' => '0', 'link' => 'DASHBOARD', 'menuexe' => '', 'menukode' => '0', 'modifiedby' => '',]);
        Menu::create(['menuname' => 'LOGOUT', 'menuseq' => '9', 'menuparent' => '0', 'menuicon' => 'FAS FA-SIGN-OUT-ALT', 'aco_id' => '0', 'link' => 'LOGOUT', 'menuexe' => '', 'menukode' => 'Z', 'modifiedby' => '',]);
        Menu::create(['menuname' => 'MASTER', 'menuseq' => '1', 'menuparent' => '0', 'menuicon' => 'FAS FA-USER-TAG', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '1', 'modifiedby' => '',]);
        Menu::create(['menuname' => 'SYSTEM', 'menuseq' => '11', 'menuparent' => '3', 'menuicon' => 'FAB FA-UBUNTU', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '11', 'modifiedby' => '',]);
        Menu::create(['menuname' => 'PARAMETER', 'menuseq' => '111', 'menuparent' => '4', 'menuicon' => 'FAS FA-EXCLAMATION', 'aco_id' => '1', 'link' => '', 'menuexe' => '', 'menukode' => '111', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'USER', 'menuseq' => '112', 'menuparent' => '4', 'menuicon' => 'FAS FA-USER', 'aco_id' => '5', 'link' => '', 'menuexe' => '', 'menukode' => '112', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'MENU', 'menuseq' => '113', 'menuparent' => '4', 'menuicon' => 'FAS FA-BARS', 'aco_id' => '9', 'link' => '', 'menuexe' => '', 'menukode' => '113', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'ROLE', 'menuseq' => '114', 'menuparent' => '4', 'menuicon' => 'FAS FA-USER-TAG', 'aco_id' => '13', 'link' => '', 'menuexe' => '', 'menukode' => '114', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'USER ACL', 'menuseq' => '115', 'menuparent' => '4', 'menuicon' => 'FAS FA-USER-TAG', 'aco_id' => '17', 'link' => '', 'menuexe' => '', 'menukode' => '115', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'USER ROLE', 'menuseq' => '116', 'menuparent' => '4', 'menuicon' => 'FAS FA-USER-TAG', 'aco_id' => '21', 'link' => '', 'menuexe' => '', 'menukode' => '116', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'ACL', 'menuseq' => '117', 'menuparent' => '4', 'menuicon' => 'FAS FA-USER-MINUS', 'aco_id' => '25', 'link' => '', 'menuexe' => '', 'menukode' => '117', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'ERROR', 'menuseq' => '118', 'menuparent' => '4', 'menuicon' => 'FAS FA-BUG', 'aco_id' => '29', 'link' => '', 'menuexe' => '', 'menukode' => '118', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'GENERAL', 'menuseq' => '12', 'menuparent' => '3', 'menuicon' => 'FAS FA-CLINIC-MEDICAL', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '12', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'CABANG', 'menuseq' => '121', 'menuparent' => '13', 'menuicon' => 'FAS FA-CODE-BRANCH', 'aco_id' => '33', 'link' => '', 'menuexe' => '', 'menukode' => '121', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'TRUCKING', 'menuseq' => '2', 'menuparent' => '0', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '2', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'ABSENSI SUPIR', 'menuseq' => '211', 'menuparent' => '119', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '37', 'link' => '', 'menuexe' => '', 'menukode' => '211', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'TRUCK', 'menuseq' => '13', 'menuparent' => '3', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '13', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'AGEN', 'menuseq' => '182', 'menuparent' => '55', 'menuicon' => 'FAS FA-USER', 'aco_id' => '41', 'link' => '', 'menuexe' => '', 'menukode' => '182', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'ABSEN TRADO', 'menuseq' => '138', 'menuparent' => '17', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '45', 'link' => '', 'menuexe' => '', 'menukode' => '138', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'COA', 'menuseq' => '15', 'menuparent' => '3', 'menuicon' => 'FAS FA-USER-TAG', 'aco_id' => '49', 'link' => '', 'menuexe' => '', 'menukode' => '15', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'ALAT BAYAR', 'menuseq' => '171', 'menuparent' => '54', 'menuicon' => 'FAS FA-BARS', 'aco_id' => '53', 'link' => '', 'menuexe' => '', 'menukode' => '171', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'BANK', 'menuseq' => '172', 'menuparent' => '54', 'menuicon' => 'FAS FA-CLINIC-MEDICAL', 'aco_id' => '57', 'link' => '', 'menuexe' => '', 'menukode' => '172', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'BANK PELANGGAN', 'menuseq' => '173', 'menuparent' => '54', 'menuicon' => 'FAS FA-CLINIC-MEDICAL', 'aco_id' => '61', 'link' => '', 'menuexe' => '', 'menukode' => '173', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'CONTAINER', 'menuseq' => '1321', 'menuparent' => '117', 'menuicon' => 'FAS FA-BARS', 'aco_id' => '65', 'link' => '', 'menuexe' => '', 'menukode' => '1321', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'GUDANG', 'menuseq' => '141', 'menuparent' => '34', 'menuicon' => 'FAS FA-CLINIC-MEDICAL', 'aco_id' => '69', 'link' => '', 'menuexe' => '', 'menukode' => '141', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'JENIS EMKL', 'menuseq' => '136', 'menuparent' => '17', 'menuicon' => 'FAS FA-CLINIC-MEDICAL', 'aco_id' => '73', 'link' => '', 'menuexe' => '', 'menukode' => '136', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'JENIS ORDERAN', 'menuseq' => '137', 'menuparent' => '17', 'menuicon' => 'FAS FA-CLINIC-MEDICAL', 'aco_id' => '77', 'link' => '', 'menuexe' => '', 'menukode' => '137', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'JENIS TRADO', 'menuseq' => '1313', 'menuparent' => '113', 'menuicon' => 'FAS FA-CLINIC-MEDICAL', 'aco_id' => '81', 'link' => '', 'menuexe' => '', 'menukode' => '1313', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'KAS/BANK', 'menuseq' => '3', 'menuparent' => '0', 'menuicon' => 'FAB FA-UBUNTU', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '3', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'KAS GANTUNG', 'menuseq' => '311', 'menuparent' => '111', 'menuicon' => 'FAS FA-CODE-BRANCH', 'aco_id' => '85', 'link' => '', 'menuexe' => '', 'menukode' => '311', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'KATEGORI', 'menuseq' => '143', 'menuparent' => '34', 'menuicon' => 'FAS FA-CODE-BRANCH', 'aco_id' => '89', 'link' => '', 'menuexe' => '', 'menukode' => '143', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'KELOMPOK', 'menuseq' => '142', 'menuparent' => '34', 'menuicon' => 'FAS FA-CODE-BRANCH', 'aco_id' => '93', 'link' => '', 'menuexe' => '', 'menukode' => '142', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'KERUSAKAN', 'menuseq' => '1314', 'menuparent' => '113', 'menuicon' => 'FAS FA-CODE-BRANCH', 'aco_id' => '97', 'link' => '', 'menuexe' => '', 'menukode' => '1314', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'STOK', 'menuseq' => '14', 'menuparent' => '3', 'menuicon' => 'FAS FA-CODE-BRANCH', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '14', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'KOTA', 'menuseq' => '1341', 'menuparent' => '115', 'menuicon' => 'FAS FA-CODE-BRANCH', 'aco_id' => '101', 'link' => '', 'menuexe' => '', 'menukode' => '1341', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'MANDOR', 'menuseq' => '1316', 'menuparent' => '113', 'menuicon' => 'FAS FA-CODE-BRANCH', 'aco_id' => '105', 'link' => '', 'menuexe' => '', 'menukode' => '1316', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'MEKANIK', 'menuseq' => '1315', 'menuparent' => '113', 'menuicon' => 'FAS FA-CODE-BRANCH', 'aco_id' => '109', 'link' => '', 'menuexe' => '', 'menukode' => '1315', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'MERK', 'menuseq' => '144', 'menuparent' => '34', 'menuicon' => 'FAS FA-CODE-BRANCH', 'aco_id' => '113', 'link' => '', 'menuexe' => '', 'menukode' => '144', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'ORDERAN TRUCKING', 'menuseq' => '221', 'menuparent' => '118', 'menuicon' => 'FAS FA-CODE-BRANCH', 'aco_id' => '117', 'link' => '', 'menuexe' => '', 'menukode' => '221', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'PELANGGAN', 'menuseq' => '184', 'menuparent' => '55', 'menuicon' => 'FAS FA-CODE-BRANCH', 'aco_id' => '121', 'link' => '', 'menuexe' => '', 'menukode' => '184', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'PENERIMA', 'menuseq' => '183', 'menuparent' => '55', 'menuicon' => 'FAS FA-USER-TAG', 'aco_id' => '125', 'link' => '', 'menuexe' => '', 'menukode' => '183', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'PENERIMAAN TRUCKING', 'menuseq' => '1351', 'menuparent' => '116', 'menuicon' => 'FAS FA-USER-MINUS', 'aco_id' => '129', 'link' => '', 'menuexe' => '', 'menukode' => '1351', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'PENGELUARAN TRUCKING', 'menuseq' => '1353', 'menuparent' => '116', 'menuicon' => 'FAS FA-USER-MINUS', 'aco_id' => '133', 'link' => '', 'menuexe' => '', 'menukode' => '1353', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'PROSES ABSENSI SUPIR', 'menuseq' => '212', 'menuparent' => '119', 'menuicon' => 'FAS FA-USER-TAG', 'aco_id' => '137', 'link' => '', 'menuexe' => '', 'menukode' => '212', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'RITASI', 'menuseq' => '28', 'menuparent' => '15', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '141', 'link' => '', 'menuexe' => '', 'menukode' => '28', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'SATUAN', 'menuseq' => '145', 'menuparent' => '34', 'menuicon' => 'FAS FA-USER', 'aco_id' => '145', 'link' => '', 'menuexe' => '', 'menukode' => '145', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'SURAT PENGANTAR', 'menuseq' => '222', 'menuparent' => '118', 'menuicon' => 'FAS FA-USER', 'aco_id' => '149', 'link' => '', 'menuexe' => '', 'menukode' => '222', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'SUPPLIER', 'menuseq' => '181', 'menuparent' => '55', 'menuicon' => 'FAS FA-USER', 'aco_id' => '153', 'link' => '', 'menuexe' => '', 'menukode' => '181', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'SUPIR', 'menuseq' => '1317', 'menuparent' => '113', 'menuicon' => 'FAS FA-USER', 'aco_id' => '157', 'link' => '', 'menuexe' => '', 'menukode' => '1317', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'SUB KELOMPOK', 'menuseq' => '146', 'menuparent' => '34', 'menuicon' => 'FAS FA-USER-TAG', 'aco_id' => '161', 'link' => '', 'menuexe' => '', 'menukode' => '146', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'STATUS CONTAINER', 'menuseq' => '1322', 'menuparent' => '117', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '165', 'link' => '', 'menuexe' => '', 'menukode' => '1322', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'TARIF', 'menuseq' => '1331', 'menuparent' => '114', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '169', 'link' => '', 'menuexe' => '', 'menukode' => '1331', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'JURNAL UMUM', 'menuseq' => '16', 'menuparent' => '3', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '16', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'KAS/BANK', 'menuseq' => '17', 'menuparent' => '3', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '17', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'RELASI', 'menuseq' => '18', 'menuparent' => '3', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '18', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'TRADO', 'menuseq' => '1311', 'menuparent' => '113', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '173', 'link' => '', 'menuexe' => '', 'menukode' => '1311', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'UPAH SUPIR', 'menuseq' => '1332', 'menuparent' => '114', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '177', 'link' => '', 'menuexe' => '', 'menukode' => '1332', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'UPAH RITASI', 'menuseq' => '1333', 'menuparent' => '114', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '181', 'link' => '', 'menuexe' => '', 'menukode' => '1333', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'ZONA', 'menuseq' => '1342', 'menuparent' => '115', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '185', 'link' => '', 'menuexe' => '', 'menukode' => '1342', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'GENERAL LEDGER', 'menuseq' => '4', 'menuparent' => '0', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '4', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'JURNAL UMUM', 'menuseq' => '41', 'menuparent' => '60', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '189', 'link' => '', 'menuexe' => '', 'menukode' => '41', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'PENERIMAAN', 'menuseq' => '321', 'menuparent' => '108', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '194', 'link' => '', 'menuexe' => '', 'menukode' => '321', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'HUTANG/PIUTANG', 'menuseq' => '5', 'menuparent' => '0', 'menuicon' => 'FAS FA-MONEY-CHECK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '5', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'PIUTANG', 'menuseq' => '511', 'menuparent' => '124', 'menuicon' => 'FAS FA-MONEY-CHECK', 'aco_id' => '198', 'link' => '', 'menuexe' => '', 'menukode' => 'A1', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'PELUNASAN PIUTANG', 'menuseq' => '512', 'menuparent' => '124', 'menuicon' => 'FAS FA-MONEY-CHECK', 'aco_id' => '202', 'link' => '', 'menuexe' => '', 'menukode' => 'A2', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'PENGELUARAN TRUCKING', 'menuseq' => '27', 'menuparent' => '15', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '206', 'link' => '', 'menuexe' => '', 'menukode' => '27', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'SERVICE IN', 'menuseq' => '251', 'menuparent' => '122', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '210', 'link' => '', 'menuexe' => '', 'menukode' => '251', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'SERVICE OUT', 'menuseq' => '252', 'menuparent' => '122', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '214', 'link' => '', 'menuexe' => '', 'menukode' => '252', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'PENERIMAAN STOK', 'menuseq' => '147', 'menuparent' => '34', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '218', 'link' => '', 'menuexe' => '', 'menukode' => '147', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'PENGELUARAN STOK', 'menuseq' => '148', 'menuparent' => '34', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '222', 'link' => '', 'menuexe' => '', 'menukode' => '148', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'RINCIAN GAJI SUPIR', 'menuseq' => '231', 'menuparent' => '120', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '226', 'link' => '', 'menuexe' => '', 'menukode' => '231', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'PROSES GAJI SUPIR', 'menuseq' => '232', 'menuparent' => '120', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '230', 'link' => '', 'menuexe' => '', 'menukode' => '232', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'ABSENSI SUPIR APPROVAL', 'menuseq' => '213', 'menuparent' => '119', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '234', 'link' => '', 'menuexe' => '', 'menukode' => '213', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'HUTANG', 'menuseq' => '10', 'menuparent' => '123', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '238', 'link' => '', 'menuexe' => '', 'menukode' => '91', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'PEMBAYARAN HUTANG', 'menuseq' => '522', 'menuparent' => '123', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '242', 'link' => '', 'menuexe' => '', 'menukode' => '92', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'INVOICE', 'menuseq' => '241', 'menuparent' => '121', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '246', 'link' => '', 'menuexe' => '', 'menukode' => '241', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'NOTA DEBET', 'menuseq' => '531', 'menuparent' => '125', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '250', 'link' => '', 'menuexe' => '', 'menukode' => 'B1', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'NOTA KREDIT', 'menuseq' => '532', 'menuparent' => '125', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '254', 'link' => '', 'menuexe' => '', 'menukode' => 'B2', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'PENERIMAAN TRUCKING', 'menuseq' => '1352', 'menuparent' => '116', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '259', 'link' => '', 'menuexe' => '', 'menukode' => '1352', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'PENERIMAAN TRUCKING', 'menuseq' => '26', 'menuparent' => '15', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '263', 'link' => '', 'menuexe' => '', 'menukode' => '26', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'STOK', 'menuseq' => '6', 'menuparent' => '0', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '6', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'PENERIMAAN  STOK', 'menuseq' => '61', 'menuparent' => '81', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '267', 'link' => '', 'menuexe' => '', 'menukode' => '61', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'PENGELUARAN STOK', 'menuseq' => '62', 'menuparent' => '81', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '271', 'link' => '', 'menuexe' => '', 'menukode' => '62', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'PENGELUARAN', 'menuseq' => '331', 'menuparent' => '109', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '275', 'link' => '', 'menuexe' => '', 'menukode' => '331', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'PENGEMBALIAN KAS GANTUNG', 'menuseq' => '312', 'menuparent' => '111', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '279', 'link' => '', 'menuexe' => '', 'menukode' => '312', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'REKAP PENGELUARAN', 'menuseq' => '332', 'menuparent' => '109', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '283', 'link' => '', 'menuexe' => '', 'menukode' => '332', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'REKAP PENERIMAAN', 'menuseq' => '322', 'menuparent' => '108', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '288', 'link' => '', 'menuexe' => '', 'menukode' => '322', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'HARI LIBUR', 'menuseq' => '122', 'menuparent' => '13', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '293', 'link' => '', 'menuexe' => '', 'menukode' => '122', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'PENERIMAAN GIRO', 'menuseq' => '341', 'menuparent' => '110', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '297', 'link' => '', 'menuexe' => '', 'menukode' => '341', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'LOG TRAIL', 'menuseq' => '119', 'menuparent' => '4', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '301', 'link' => '', 'menuexe' => '', 'menukode' => '119', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'INVOICE EXTRA', 'menuseq' => '242', 'menuparent' => '121', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '302', 'link' => '', 'menuexe' => '', 'menukode' => '242', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'APPROVAL JURNAL', 'menuseq' => '42', 'menuparent' => '60', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '306', 'link' => '', 'menuexe' => '', 'menukode' => '42', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'APPROVAL', 'menuseq' => '7', 'menuparent' => '0', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '7', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'LAPORAN', 'menuseq' => '8', 'menuparent' => '0', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '8', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'APPROVAL KAS/BANK', 'menuseq' => '71', 'menuparent' => '93', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '309', 'link' => '', 'menuexe' => '', 'menukode' => '71', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'APPROVAL HUTANG', 'menuseq' => '72', 'menuparent' => '93', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '311', 'link' => '', 'menuexe' => '', 'menukode' => '72', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'APPROVAL NOTA', 'menuseq' => '73', 'menuparent' => '93', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '313', 'link' => '', 'menuexe' => '', 'menukode' => '73', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'APPROVAL INVOICE', 'menuseq' => '74', 'menuparent' => '93', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '315', 'link' => '', 'menuexe' => '', 'menukode' => '74', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'APPROVAL PENDAPATAN', 'menuseq' => '75', 'menuparent' => '93', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '317', 'link' => '', 'menuexe' => '', 'menukode' => '75', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'PENDAPATAN SUPIR', 'menuseq' => '76', 'menuparent' => '93', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '319', 'link' => '', 'menuexe' => '', 'menukode' => '76', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'STOK', 'menuseq' => '149', 'menuparent' => '34', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '323', 'link' => '', 'menuexe' => '', 'menukode' => '149', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'PENCAIRAN GIRO', 'menuseq' => '342', 'menuparent' => '110', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '327', 'link' => '', 'menuexe' => '', 'menukode' => '342', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'GANDENGAN', 'menuseq' => '1312', 'menuparent' => '113', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '329', 'link' => '', 'menuexe' => '', 'menukode' => '1312', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'PERSEDIAAN STOK', 'menuseq' => '63', 'menuparent' => '81', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '333', 'link' => '', 'menuexe' => '', 'menukode' => '63', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'KARTU STOK', 'menuseq' => '64', 'menuparent' => '81', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '334', 'link' => '', 'menuexe' => '', 'menukode' => '64', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'NOTA', 'menuseq' => '53', 'menuparent' => '63', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '53', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'HUTANG', 'menuseq' => '52', 'menuparent' => '63', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '52', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'PENERIMAAN KAS/BANK', 'menuseq' => '32', 'menuparent' => '29', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '32', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'PENGELUARAN KAS/BANK', 'menuseq' => '33', 'menuparent' => '29', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '33', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'GIRO', 'menuseq' => '34', 'menuparent' => '29', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '34', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'KAS GANTUNG', 'menuseq' => '31', 'menuparent' => '29', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '31', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'PIUTANG', 'menuseq' => '51', 'menuparent' => '63', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '51', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'TRADO', 'menuseq' => '131', 'menuparent' => '17', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '131', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'TARIF & UPAH', 'menuseq' => '133', 'menuparent' => '17', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '133', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'KOTA', 'menuseq' => '134', 'menuparent' => '17', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '134', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'PENOMORAN', 'menuseq' => '135', 'menuparent' => '17', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '135', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'CONTAINER', 'menuseq' => '132', 'menuparent' => '17', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '132', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'ORDERAN TRUCKING', 'menuseq' => '22', 'menuparent' => '15', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '22', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'ABSENSI SUPIR', 'menuseq' => '21', 'menuparent' => '15', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '21', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'GAJI SUPIR', 'menuseq' => '23', 'menuparent' => '15', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '23', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'INVOICE', 'menuseq' => '24', 'menuparent' => '15', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '24', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'SERVICE', 'menuseq' => '25', 'menuparent' => '15', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '25', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'HUTANG', 'menuseq' => '9', 'menuparent' => '0', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '9', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'PIUTANG', 'menuseq' => '100', 'menuparent' => '0', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => 'A', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'NOTA', 'menuseq' => '100', 'menuparent' => '0', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => 'B', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'HISTORY STOK', 'menuseq' => '100', 'menuparent' => '81', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '65', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'HISTORY PENERIMAAN', 'menuseq' => '100', 'menuparent' => '126', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '337', 'link' => '', 'menuexe' => '', 'menukode' => '651', 'modifiedby' => 'ADMIN',]);
        Menu::create(['menuname' => 'HISTORY PENGELUARAN', 'menuseq' => '100', 'menuparent' => '126', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '339', 'link' => '', 'menuexe' => '', 'menukode' => '652', 'modifiedby' => 'ADMIN',]);
    }
}
