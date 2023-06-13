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

        DB::statement("delete menu");
        DB::statement("DBCC CHECKIDENT ('menu', RESEED, 1);");

        menu::create(['menuname' => 'DASHBOARD', 'menuseq' => '0', 'menuparent' => '0', 'menuicon' => 'FAS FA-HOME', 'aco_id' => '0', 'link' => 'DASHBOARD', 'menuexe' => '', 'menukode' => '0', 'modifiedby' => '',]);
        menu::create(['menuname' => 'LOGOUT', 'menuseq' => '9', 'menuparent' => '0', 'menuicon' => 'FAS FA-SIGN-OUT-ALT', 'aco_id' => '0', 'link' => 'LOGOUT', 'menuexe' => '', 'menukode' => 'Z', 'modifiedby' => '',]);
        menu::create(['menuname' => 'MASTER', 'menuseq' => '1', 'menuparent' => '0', 'menuicon' => 'FAS FA-USER-TAG', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '1', 'modifiedby' => '',]);
        menu::create(['menuname' => 'SYSTEM', 'menuseq' => '11', 'menuparent' => '3', 'menuicon' => 'FAB FA-UBUNTU', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '11', 'modifiedby' => '',]);
        menu::create(['menuname' => 'PARAMETER', 'menuseq' => '111', 'menuparent' => '4', 'menuicon' => 'FAS FA-EXCLAMATION', 'aco_id' => '1', 'link' => '', 'menuexe' => '', 'menukode' => '111', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'USER', 'menuseq' => '112', 'menuparent' => '4', 'menuicon' => 'FAS FA-USER', 'aco_id' => '5', 'link' => '', 'menuexe' => '', 'menukode' => '112', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'MENU', 'menuseq' => '113', 'menuparent' => '4', 'menuicon' => 'FAS FA-BARS', 'aco_id' => '9', 'link' => '', 'menuexe' => '', 'menukode' => '113', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'ROLE', 'menuseq' => '114', 'menuparent' => '4', 'menuicon' => 'FAS FA-USER-TAG', 'aco_id' => '13', 'link' => '', 'menuexe' => '', 'menukode' => '114', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'USER ACL', 'menuseq' => '115', 'menuparent' => '4', 'menuicon' => 'FAS FA-USER-TAG', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '11A', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'USER ROLE', 'menuseq' => '116', 'menuparent' => '4', 'menuicon' => 'FAS FA-USER-TAG', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '11B', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'ACL', 'menuseq' => '117', 'menuparent' => '4', 'menuicon' => 'FAS FA-USER-MINUS', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '11C', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'ERROR', 'menuseq' => '118', 'menuparent' => '4', 'menuicon' => 'FAS FA-BUG', 'aco_id' => '29', 'link' => '', 'menuexe' => '', 'menukode' => '115', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'GENERAL', 'menuseq' => '12', 'menuparent' => '3', 'menuicon' => 'FAS FA-CLINIC-MEDICAL', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '12', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'CABANG', 'menuseq' => '121', 'menuparent' => '13', 'menuicon' => 'FAS FA-CODE-BRANCH', 'aco_id' => '33', 'link' => '', 'menuexe' => '', 'menukode' => '121', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'TRUCKING', 'menuseq' => '2', 'menuparent' => '0', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '2', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'ABSENSI SUPIR (ADMIN)', 'menuseq' => '211', 'menuparent' => '105', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '37', 'link' => '', 'menuexe' => '', 'menukode' => '211', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'TRUCK', 'menuseq' => '13', 'menuparent' => '3', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '13', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'AGEN', 'menuseq' => '182', 'menuparent' => '53', 'menuicon' => 'FAS FA-USER', 'aco_id' => '41', 'link' => '', 'menuexe' => '', 'menukode' => '182', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'ABSEN TRADO', 'menuseq' => '138', 'menuparent' => '17', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '45', 'link' => '', 'menuexe' => '', 'menukode' => '138', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'COA', 'menuseq' => '15', 'menuparent' => '3', 'menuicon' => 'FAS FA-USER-TAG', 'aco_id' => '49', 'link' => '', 'menuexe' => '', 'menukode' => '15', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'ALAT BAYAR', 'menuseq' => '171', 'menuparent' => '52', 'menuicon' => 'FAS FA-BARS', 'aco_id' => '53', 'link' => '', 'menuexe' => '', 'menukode' => '171', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'KAS/BANK', 'menuseq' => '172', 'menuparent' => '52', 'menuicon' => 'FAS FA-CLINIC-MEDICAL', 'aco_id' => '57', 'link' => '', 'menuexe' => '', 'menukode' => '172', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'BANK PELANGGAN', 'menuseq' => '173', 'menuparent' => '52', 'menuicon' => 'FAS FA-CLINIC-MEDICAL', 'aco_id' => '61', 'link' => '', 'menuexe' => '', 'menukode' => '173', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'CONTAINER', 'menuseq' => '1321', 'menuparent' => '103', 'menuicon' => 'FAS FA-BARS', 'aco_id' => '65', 'link' => '', 'menuexe' => '', 'menukode' => '1321', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'GUDANG', 'menuseq' => '141', 'menuparent' => '34', 'menuicon' => 'FAS FA-CLINIC-MEDICAL', 'aco_id' => '69', 'link' => '', 'menuexe' => '', 'menukode' => '141', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'JENIS EMKL', 'menuseq' => '136', 'menuparent' => '17', 'menuicon' => 'FAS FA-CLINIC-MEDICAL', 'aco_id' => '73', 'link' => '', 'menuexe' => '', 'menukode' => '136', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'JENIS ORDERAN', 'menuseq' => '137', 'menuparent' => '17', 'menuicon' => 'FAS FA-CLINIC-MEDICAL', 'aco_id' => '77', 'link' => '', 'menuexe' => '', 'menukode' => '137', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'JENIS TRADO', 'menuseq' => '1313', 'menuparent' => '99', 'menuicon' => 'FAS FA-CLINIC-MEDICAL', 'aco_id' => '81', 'link' => '', 'menuexe' => '', 'menukode' => '1313', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'KAS/BANK', 'menuseq' => '3', 'menuparent' => '0', 'menuicon' => 'FAB FA-UBUNTU', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '3', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'KAS GANTUNG', 'menuseq' => '311', 'menuparent' => '98', 'menuicon' => 'FAS FA-CODE-BRANCH', 'aco_id' => '85', 'link' => '', 'menuexe' => '', 'menukode' => '311', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'KATEGORI', 'menuseq' => '143', 'menuparent' => '34', 'menuicon' => 'FAS FA-CODE-BRANCH', 'aco_id' => '89', 'link' => '', 'menuexe' => '', 'menukode' => '143', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'KELOMPOK', 'menuseq' => '142', 'menuparent' => '34', 'menuicon' => 'FAS FA-CODE-BRANCH', 'aco_id' => '93', 'link' => '', 'menuexe' => '', 'menukode' => '142', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'KERUSAKAN', 'menuseq' => '1314', 'menuparent' => '99', 'menuicon' => 'FAS FA-CODE-BRANCH', 'aco_id' => '97', 'link' => '', 'menuexe' => '', 'menukode' => '1314', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'STOK', 'menuseq' => '14', 'menuparent' => '3', 'menuicon' => 'FAS FA-CODE-BRANCH', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '14', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'KOTA', 'menuseq' => '1341', 'menuparent' => '101', 'menuicon' => 'FAS FA-CODE-BRANCH', 'aco_id' => '101', 'link' => '', 'menuexe' => '', 'menukode' => '1341', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'MANDOR', 'menuseq' => '1316', 'menuparent' => '99', 'menuicon' => 'FAS FA-CODE-BRANCH', 'aco_id' => '105', 'link' => '', 'menuexe' => '', 'menukode' => '1316', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'MERK', 'menuseq' => '144', 'menuparent' => '34', 'menuicon' => 'FAS FA-CODE-BRANCH', 'aco_id' => '113', 'link' => '', 'menuexe' => '', 'menukode' => '144', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'ORDERAN TRUCKING', 'menuseq' => '221', 'menuparent' => '104', 'menuicon' => 'FAS FA-CODE-BRANCH', 'aco_id' => '117', 'link' => '', 'menuexe' => '', 'menukode' => '221', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'PELANGGAN', 'menuseq' => '184', 'menuparent' => '53', 'menuicon' => 'FAS FA-CODE-BRANCH', 'aco_id' => '121', 'link' => '', 'menuexe' => '', 'menukode' => '184', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'PENERIMA', 'menuseq' => '183', 'menuparent' => '53', 'menuicon' => 'FAS FA-USER-TAG', 'aco_id' => '125', 'link' => '', 'menuexe' => '', 'menukode' => '183', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'PENERIMAAN TRUCKING', 'menuseq' => '1351', 'menuparent' => '102', 'menuicon' => 'FAS FA-USER-MINUS', 'aco_id' => '129', 'link' => '', 'menuexe' => '', 'menukode' => '1351', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'PENGELUARAN TRUCKING', 'menuseq' => '1353', 'menuparent' => '102', 'menuicon' => 'FAS FA-USER-MINUS', 'aco_id' => '133', 'link' => '', 'menuexe' => '', 'menukode' => '1352', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'RITASI', 'menuseq' => '28', 'menuparent' => '15', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '141', 'link' => '', 'menuexe' => '', 'menukode' => '28', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'SATUAN', 'menuseq' => '145', 'menuparent' => '34', 'menuicon' => 'FAS FA-USER', 'aco_id' => '145', 'link' => '', 'menuexe' => '', 'menukode' => '145', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'SURAT PENGANTAR', 'menuseq' => '222', 'menuparent' => '104', 'menuicon' => 'FAS FA-USER', 'aco_id' => '149', 'link' => '', 'menuexe' => '', 'menukode' => '222', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'SUPPLIER', 'menuseq' => '181', 'menuparent' => '53', 'menuicon' => 'FAS FA-USER', 'aco_id' => '153', 'link' => '', 'menuexe' => '', 'menukode' => '181', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'SUPIR', 'menuseq' => '1317', 'menuparent' => '99', 'menuicon' => 'FAS FA-USER', 'aco_id' => '157', 'link' => '', 'menuexe' => '', 'menukode' => '1317', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'SUB KELOMPOK', 'menuseq' => '146', 'menuparent' => '34', 'menuicon' => 'FAS FA-USER-TAG', 'aco_id' => '161', 'link' => '', 'menuexe' => '', 'menukode' => '146', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'STATUS CONTAINER', 'menuseq' => '1322', 'menuparent' => '103', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '165', 'link' => '', 'menuexe' => '', 'menukode' => '1322', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'TARIF', 'menuseq' => '1331', 'menuparent' => '100', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '169', 'link' => '', 'menuexe' => '', 'menukode' => '1331', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'JURNAL UMUM', 'menuseq' => '16', 'menuparent' => '3', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '16', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'KAS/BANK', 'menuseq' => '17', 'menuparent' => '3', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '17', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'RELASI', 'menuseq' => '18', 'menuparent' => '3', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '18', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'TRADO', 'menuseq' => '1311', 'menuparent' => '99', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '173', 'link' => '', 'menuexe' => '', 'menukode' => '1311', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'UPAH SUPIR', 'menuseq' => '1332', 'menuparent' => '100', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '177', 'link' => '', 'menuexe' => '', 'menukode' => '1332', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'UPAH RITASI', 'menuseq' => '1333', 'menuparent' => '100', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '181', 'link' => '', 'menuexe' => '', 'menukode' => '1333', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'ZONA', 'menuseq' => '1342', 'menuparent' => '101', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '185', 'link' => '', 'menuexe' => '', 'menukode' => '1342', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'GENERAL LEDGER', 'menuseq' => '4', 'menuparent' => '0', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '4', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'JURNAL UMUM', 'menuseq' => '41', 'menuparent' => '58', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '189', 'link' => '', 'menuexe' => '', 'menukode' => '41', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'PENERIMAAN', 'menuseq' => '321', 'menuparent' => '95', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '194', 'link' => '', 'menuexe' => '', 'menukode' => '321', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'PIUTANG', 'menuseq' => '511', 'menuparent' => '110', 'menuicon' => 'FAS FA-MONEY-CHECK', 'aco_id' => '198', 'link' => '', 'menuexe' => '', 'menukode' => '81', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'PELUNASAN PIUTANG', 'menuseq' => '512', 'menuparent' => '110', 'menuicon' => 'FAS FA-MONEY-CHECK', 'aco_id' => '202', 'link' => '', 'menuexe' => '', 'menukode' => '82', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'PENGELUARAN TRUCKING', 'menuseq' => '27', 'menuparent' => '15', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '206', 'link' => '', 'menuexe' => '', 'menukode' => '27', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'SERVICE IN', 'menuseq' => '251', 'menuparent' => '108', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '210', 'link' => '', 'menuexe' => '', 'menukode' => '251', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'SERVICE OUT', 'menuseq' => '252', 'menuparent' => '108', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '214', 'link' => '', 'menuexe' => '', 'menukode' => '252', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'PENERIMAAN STOK', 'menuseq' => '147', 'menuparent' => '34', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '218', 'link' => '', 'menuexe' => '', 'menukode' => '147', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'PENGELUARAN STOK', 'menuseq' => '148', 'menuparent' => '34', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '222', 'link' => '', 'menuexe' => '', 'menukode' => '148', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'RINCIAN GAJI SUPIR', 'menuseq' => '231', 'menuparent' => '106', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '226', 'link' => '', 'menuexe' => '', 'menukode' => '231', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'PROSES GAJI SUPIR', 'menuseq' => '232', 'menuparent' => '106', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '230', 'link' => '', 'menuexe' => '', 'menukode' => '232', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'ABSENSI SUPIR POSTING (KEUANGAN)', 'menuseq' => '213', 'menuparent' => '105', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '234', 'link' => '', 'menuexe' => '', 'menukode' => '212', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'HUTANG', 'menuseq' => '10', 'menuparent' => '109', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '238', 'link' => '', 'menuexe' => '', 'menukode' => '71', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'PEMBAYARAN HUTANG', 'menuseq' => '522', 'menuparent' => '109', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '242', 'link' => '', 'menuexe' => '', 'menukode' => '72', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'INVOICE', 'menuseq' => '241', 'menuparent' => '107', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '246', 'link' => '', 'menuexe' => '', 'menukode' => '241', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'NOTA DEBET', 'menuseq' => '531', 'menuparent' => '111', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '250', 'link' => '', 'menuexe' => '', 'menukode' => '91', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'NOTA KREDIT', 'menuseq' => '532', 'menuparent' => '111', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '254', 'link' => '', 'menuexe' => '', 'menukode' => '92', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'PENERIMAAN TRUCKING', 'menuseq' => '26', 'menuparent' => '15', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '263', 'link' => '', 'menuexe' => '', 'menukode' => '26', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'STOK', 'menuseq' => '6', 'menuparent' => '0', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '5', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'PENERIMAAN  STOK', 'menuseq' => '61', 'menuparent' => '77', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '267', 'link' => '', 'menuexe' => '', 'menukode' => '51', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'PENGELUARAN STOK', 'menuseq' => '62', 'menuparent' => '77', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '271', 'link' => '', 'menuexe' => '', 'menukode' => '52', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'PENGELUARAN', 'menuseq' => '331', 'menuparent' => '96', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '275', 'link' => '', 'menuexe' => '', 'menukode' => '331', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'PENGEMBALIAN KAS GANTUNG', 'menuseq' => '312', 'menuparent' => '98', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '279', 'link' => '', 'menuexe' => '', 'menukode' => '312', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'REKAP PENGELUARAN', 'menuseq' => '332', 'menuparent' => '96', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '283', 'link' => '', 'menuexe' => '', 'menukode' => '332', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'REKAP PENERIMAAN', 'menuseq' => '322', 'menuparent' => '95', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '288', 'link' => '', 'menuexe' => '', 'menukode' => '322', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'HARI LIBUR', 'menuseq' => '122', 'menuparent' => '13', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '293', 'link' => '', 'menuexe' => '', 'menukode' => '122', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'PENERIMAAN GIRO', 'menuseq' => '341', 'menuparent' => '97', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '297', 'link' => '', 'menuexe' => '', 'menukode' => '341', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'LOG TRAIL', 'menuseq' => '119', 'menuparent' => '4', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '301', 'link' => '', 'menuexe' => '', 'menukode' => '116', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'INVOICE EXTRA', 'menuseq' => '242', 'menuparent' => '107', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '302', 'link' => '', 'menuexe' => '', 'menukode' => '242', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'LAPORAN', 'menuseq' => '8', 'menuparent' => '0', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '6', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'PENDAPATAN SUPIR', 'menuseq' => '76', 'menuparent' => '15', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '319', 'link' => '', 'menuexe' => '', 'menukode' => '76', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'STOK', 'menuseq' => '149', 'menuparent' => '34', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '323', 'link' => '', 'menuexe' => '', 'menukode' => '149', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'PENCAIRAN GIRO', 'menuseq' => '342', 'menuparent' => '97', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '327', 'link' => '', 'menuexe' => '', 'menukode' => '342', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'GANDENGAN', 'menuseq' => '1312', 'menuparent' => '99', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '329', 'link' => '', 'menuexe' => '', 'menukode' => '1312', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'PERSEDIAAN STOK', 'menuseq' => '63', 'menuparent' => '77', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '333', 'link' => '', 'menuexe' => '', 'menukode' => '53', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'KARTU STOK', 'menuseq' => '64', 'menuparent' => '77', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '334', 'link' => '', 'menuexe' => '', 'menukode' => '54', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'PENERIMAAN KAS/BANK', 'menuseq' => '32', 'menuparent' => '29', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '32', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'PENGELUARAN KAS/BANK', 'menuseq' => '33', 'menuparent' => '29', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '33', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'GIRO', 'menuseq' => '34', 'menuparent' => '29', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '34', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'KAS GANTUNG', 'menuseq' => '31', 'menuparent' => '29', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '31', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'TRADO', 'menuseq' => '131', 'menuparent' => '17', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '131', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'TARIF & UPAH', 'menuseq' => '133', 'menuparent' => '17', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '133', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'KOTA', 'menuseq' => '134', 'menuparent' => '17', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '134', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'PENOMORAN', 'menuseq' => '135', 'menuparent' => '17', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '135', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'CONTAINER', 'menuseq' => '132', 'menuparent' => '17', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '132', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'ORDERAN TRUCKING', 'menuseq' => '22', 'menuparent' => '15', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '22', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'ABSENSI SUPIR', 'menuseq' => '21', 'menuparent' => '15', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '21', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'GAJI SUPIR', 'menuseq' => '23', 'menuparent' => '15', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '23', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'INVOICE', 'menuseq' => '24', 'menuparent' => '15', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '24', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'SERVICE', 'menuseq' => '25', 'menuparent' => '15', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '25', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'HUTANG', 'menuseq' => '9', 'menuparent' => '0', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '7', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'PIUTANG', 'menuseq' => '100', 'menuparent' => '0', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '8', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'NOTA', 'menuseq' => '100', 'menuparent' => '0', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '9', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'HISTORY STOK', 'menuseq' => '100', 'menuparent' => '77', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '55', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'HISTORY PENERIMAAN', 'menuseq' => '100', 'menuparent' => '112', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '337', 'link' => '', 'menuexe' => '', 'menukode' => '551', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'HISTORY PENGELUARAN', 'menuseq' => '100', 'menuparent' => '112', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '339', 'link' => '', 'menuexe' => '', 'menukode' => '552', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'ABSENSI SUPIR (MANDOR)', 'menuseq' => '100', 'menuparent' => '105', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '341', 'link' => '', 'menuexe' => '', 'menukode' => '213', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'INPUT TRIP (MANDOR)', 'menuseq' => '100', 'menuparent' => '104', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '345', 'link' => '', 'menuexe' => '', 'menukode' => '223', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'DATA TRIP (MANDOR)', 'menuseq' => '100', 'menuparent' => '104', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '350', 'link' => '', 'menuexe' => '', 'menukode' => '224', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'HISTORY TRIP (MANDOR)', 'menuseq' => '100', 'menuparent' => '104', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '351', 'link' => '', 'menuexe' => '', 'menukode' => '225', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'PROSES UANG JALAN SUPIR', 'menuseq' => '100', 'menuparent' => '15', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '352', 'link' => '', 'menuexe' => '', 'menukode' => '29', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'TOOL', 'menuseq' => '100', 'menuparent' => '0', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => 'A', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'TUTUP BUKU', 'menuseq' => '100', 'menuparent' => '120', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '360', 'link' => '', 'menuexe' => '', 'menukode' => 'A1', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'LAP. BUKU BESAR', 'menuseq' => '100', 'menuparent' => '143', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '362', 'link' => '', 'menuexe' => '', 'menukode' => '621', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'LAP. DEPOSITO SUPIR', 'menuseq' => '100', 'menuparent' => '147', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '364', 'link' => '', 'menuexe' => '', 'menukode' => '66H', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'LAP. ESTIMASI KAS GANTUNG', 'menuseq' => '100', 'menuparent' => '147', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '366', 'link' => '', 'menuexe' => '', 'menukode' => '66I', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'LAP. HISTORY PINJAMAN', 'menuseq' => '100', 'menuparent' => '147', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '367', 'link' => '', 'menuexe' => '', 'menukode' => '66J', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'LAP. HUTANG BBM', 'menuseq' => '100', 'menuparent' => '147', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '369', 'link' => '', 'menuexe' => '', 'menukode' => '661', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'LAP. KAS BANK', 'menuseq' => '100', 'menuparent' => '142', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '371', 'link' => '', 'menuexe' => '', 'menukode' => '611', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'LAP. KAS GANTUNG', 'menuseq' => '100', 'menuparent' => '147', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '372', 'link' => '', 'menuexe' => '', 'menukode' => '662', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'LAP. KETERANGAN PINJAMAN', 'menuseq' => '100', 'menuparent' => '147', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '373', 'link' => '', 'menuexe' => '', 'menukode' => '663', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'LAP. KLAIM PJT SUPIR', 'menuseq' => '100', 'menuparent' => '147', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '375', 'link' => '', 'menuexe' => '', 'menukode' => '664', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'LAP. PEMOTONGAN PINJAMAN DEPOSITO', 'menuseq' => '100', 'menuparent' => '147', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '377', 'link' => '', 'menuexe' => '', 'menukode' => '665', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'LAP. PEMOTONGAN PINJAMAN PER EBS', 'menuseq' => '100', 'menuparent' => '147', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '379', 'link' => '', 'menuexe' => '', 'menukode' => '666', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'LAP. PINJAMAN SUPIR', 'menuseq' => '100', 'menuparent' => '147', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '381', 'link' => '', 'menuexe' => '', 'menukode' => '667', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'LAP. PINJAMAN KARYAWAN', 'menuseq' => '100', 'menuparent' => '147', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '383', 'link' => '', 'menuexe' => '', 'menukode' => '668', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'LAP. REKAP SUMBANGAN', 'menuseq' => '100', 'menuparent' => '147', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '385', 'link' => '', 'menuexe' => '', 'menukode' => '669', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'LAP. RITASI GANDENGAN', 'menuseq' => '100', 'menuparent' => '147', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '387', 'link' => '', 'menuexe' => '', 'menukode' => '66A', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'LAP. RITASI TRADO', 'menuseq' => '100', 'menuparent' => '147', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '388', 'link' => '', 'menuexe' => '', 'menukode' => '66B', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'LAP. SUPIR LEBIH DARI TRADO', 'menuseq' => '100', 'menuparent' => '147', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '390', 'link' => '', 'menuexe' => '', 'menukode' => '66D', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'LAP. TRIP GANDENGAN', 'menuseq' => '100', 'menuparent' => '147', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '392', 'link' => '', 'menuexe' => '', 'menukode' => '66E', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'LAP. TRIP TRADO', 'menuseq' => '100', 'menuparent' => '147', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '394', 'link' => '', 'menuexe' => '', 'menukode' => '66F', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'LAP. UANG JALAN', 'menuseq' => '100', 'menuparent' => '147', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '396', 'link' => '', 'menuexe' => '', 'menukode' => '66G', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'LAP. KAS/BANK', 'menuseq' => '100', 'menuparent' => '88', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '61', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'LAP. JURNAL UMUM', 'menuseq' => '100', 'menuparent' => '88', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '62', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'LAP. HUTANG/PIUTANG', 'menuseq' => '100', 'menuparent' => '88', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '63', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'LAP. PEMBELIAN', 'menuseq' => '100', 'menuparent' => '88', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '64', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'LAP. STOK', 'menuseq' => '100', 'menuparent' => '88', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '65', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'LAP. TRUCKING', 'menuseq' => '100', 'menuparent' => '88', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '66', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'BUKA TANGGAL ABSENSI', 'menuseq' => '100', 'menuparent' => '105', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '400', 'link' => '', 'menuexe' => '', 'menukode' => '214', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'PEMUTIHAN SUPIR', 'menuseq' => '100', 'menuparent' => '15', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '405', 'link' => '', 'menuexe' => '', 'menukode' => '2A', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'INVOICE CHARGE GANDENGAN', 'menuseq' => '100', 'menuparent' => '107', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '409', 'link' => '', 'menuexe' => '', 'menukode' => '243', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'KARYAWAN', 'menuseq' => '2', 'menuparent' => '13', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '413', 'link' => '', 'menuexe' => '', 'menukode' => '123', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'APPROVAL', 'menuseq' => '100', 'menuparent' => '0', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => 'B', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'APPROVAL BUKA CETAK ULANG', 'menuseq' => '100', 'menuparent' => '152', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '441', 'link' => '', 'menuexe' => '', 'menukode' => 'B1', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'DATA RITASI', 'menuseq' => '100', 'menuparent' => '17', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '443', 'link' => '', 'menuexe' => '', 'menukode' => '139', 'modifiedby' => 'ADMIN',]);
    }
}
