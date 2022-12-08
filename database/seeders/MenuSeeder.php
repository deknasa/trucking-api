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

        menu::create(['menuname' => 'DASHBOARD', 'menuseq' => '0', 'menuparent' => '0', 'menuicon' => 'FAS FA-HOME', 'aco_id' => '0', 'link' => 'DASHBOARD', 'menuexe' => '', 'menukode' => '0', 'modifiedby' => '',]);
        menu::create(['menuname' => 'LOGOUT', 'menuseq' => '7', 'menuparent' => '0', 'menuicon' => 'FAS FA-SIGN-OUT-ALT', 'aco_id' => '0', 'link' => 'LOGOUT', 'menuexe' => '', 'menukode' => '9', 'modifiedby' => '',]);
        menu::create(['menuname' => 'MASTER', 'menuseq' => '1', 'menuparent' => '0', 'menuicon' => 'FAS FA-USER-TAG', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '1', 'modifiedby' => '',]);
        menu::create(['menuname' => 'SYSTEM', 'menuseq' => '11', 'menuparent' => '3', 'menuicon' => 'FAB FA-UBUNTU', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '11', 'modifiedby' => '',]);
        menu::create(['menuname' => 'PARAMETER', 'menuseq' => '111', 'menuparent' => '4', 'menuicon' => 'FAS FA-EXCLAMATION', 'aco_id' => '1', 'link' => '', 'menuexe' => '', 'menukode' => '111', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'USER', 'menuseq' => '112', 'menuparent' => '4', 'menuicon' => 'FAS FA-USER', 'aco_id' => '5', 'link' => '', 'menuexe' => '', 'menukode' => '112', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'MENU', 'menuseq' => '113', 'menuparent' => '4', 'menuicon' => 'FAS FA-BARS', 'aco_id' => '9', 'link' => '', 'menuexe' => '', 'menukode' => '113', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'ROLE', 'menuseq' => '114', 'menuparent' => '4', 'menuicon' => 'FAS FA-USER-TAG', 'aco_id' => '13', 'link' => '', 'menuexe' => '', 'menukode' => '114', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'USER ACL', 'menuseq' => '115', 'menuparent' => '4', 'menuicon' => 'FAS FA-USER-TAG', 'aco_id' => '17', 'link' => '', 'menuexe' => '', 'menukode' => '115', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'USER ROLE', 'menuseq' => '116', 'menuparent' => '4', 'menuicon' => 'FAS FA-USER-TAG', 'aco_id' => '21', 'link' => '', 'menuexe' => '', 'menukode' => '116', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'ACL', 'menuseq' => '117', 'menuparent' => '4', 'menuicon' => 'FAS FA-USER-MINUS', 'aco_id' => '25', 'link' => '', 'menuexe' => '', 'menukode' => '117', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'ERROR', 'menuseq' => '118', 'menuparent' => '4', 'menuicon' => 'FAS FA-BUG', 'aco_id' => '29', 'link' => '', 'menuexe' => '', 'menukode' => '118', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'GENERAL', 'menuseq' => '12', 'menuparent' => '3', 'menuicon' => 'FAS FA-CLINIC-MEDICAL', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '12', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'CABANG', 'menuseq' => '121', 'menuparent' => '13', 'menuicon' => 'FAS FA-CODE-BRANCH', 'aco_id' => '33', 'link' => '', 'menuexe' => '', 'menukode' => '121', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'TRUCKING', 'menuseq' => '2', 'menuparent' => '0', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '2', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'ABSENSI SUPIR', 'menuseq' => '21', 'menuparent' => '15', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '37', 'link' => '', 'menuexe' => '', 'menukode' => '21', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'TRUCK', 'menuseq' => '13', 'menuparent' => '3', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '13', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'AGEN', 'menuseq' => '122', 'menuparent' => '13', 'menuicon' => 'FAS FA-USER', 'aco_id' => '41', 'link' => '', 'menuexe' => '', 'menukode' => '122', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'ABSEN TRADO', 'menuseq' => '132', 'menuparent' => '17', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '45', 'link' => '', 'menuexe' => '', 'menukode' => '132', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'AKUN PUSAT', 'menuseq' => '151', 'menuparent' => '53', 'menuicon' => 'FAS FA-USER-TAG', 'aco_id' => '49', 'link' => '', 'menuexe' => '', 'menukode' => '151', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'ALAT BAYAR', 'menuseq' => '161', 'menuparent' => '54', 'menuicon' => 'FAS FA-BARS', 'aco_id' => '53', 'link' => '', 'menuexe' => '', 'menukode' => '161', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'BANK', 'menuseq' => '162', 'menuparent' => '54', 'menuicon' => 'FAS FA-CLINIC-MEDICAL', 'aco_id' => '57', 'link' => '', 'menuexe' => '', 'menukode' => '162', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'BANK PELANGGAN', 'menuseq' => '163', 'menuparent' => '54', 'menuicon' => 'FAS FA-CLINIC-MEDICAL', 'aco_id' => '61', 'link' => '', 'menuexe' => '', 'menukode' => '163', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'CONTAINER', 'menuseq' => '133', 'menuparent' => '17', 'menuicon' => 'FAS FA-BARS', 'aco_id' => '65', 'link' => '', 'menuexe' => '', 'menukode' => '133', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'GUDANG', 'menuseq' => '141', 'menuparent' => '34', 'menuicon' => 'FAS FA-CLINIC-MEDICAL', 'aco_id' => '69', 'link' => '', 'menuexe' => '', 'menukode' => '141', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'JENIS EMKL', 'menuseq' => '134', 'menuparent' => '17', 'menuicon' => 'FAS FA-CLINIC-MEDICAL', 'aco_id' => '73', 'link' => '', 'menuexe' => '', 'menukode' => '134', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'JENIS ORDERAN', 'menuseq' => '135', 'menuparent' => '17', 'menuicon' => 'FAS FA-CLINIC-MEDICAL', 'aco_id' => '77', 'link' => '', 'menuexe' => '', 'menukode' => '135', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'JENIS TRADO', 'menuseq' => '136', 'menuparent' => '17', 'menuicon' => 'FAS FA-CLINIC-MEDICAL', 'aco_id' => '81', 'link' => '', 'menuexe' => '', 'menukode' => '136', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'TRANSAKSI KAS/BANK', 'menuseq' => '3', 'menuparent' => '0', 'menuicon' => 'FAB FA-UBUNTU', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '3', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'KAS GANTUNG', 'menuseq' => '31', 'menuparent' => '29', 'menuicon' => 'FAS FA-CODE-BRANCH', 'aco_id' => '85', 'link' => '', 'menuexe' => '', 'menukode' => '31', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'KATEGORI', 'menuseq' => '143', 'menuparent' => '34', 'menuicon' => 'FAS FA-CODE-BRANCH', 'aco_id' => '89', 'link' => '', 'menuexe' => '', 'menukode' => '143', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'KELOMPOK', 'menuseq' => '142', 'menuparent' => '34', 'menuicon' => 'FAS FA-CODE-BRANCH', 'aco_id' => '93', 'link' => '', 'menuexe' => '', 'menukode' => '142', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'KERUSAKAN', 'menuseq' => '137', 'menuparent' => '17', 'menuicon' => 'FAS FA-CODE-BRANCH', 'aco_id' => '97', 'link' => '', 'menuexe' => '', 'menukode' => '137', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'STOK', 'menuseq' => '14', 'menuparent' => '3', 'menuicon' => 'FAS FA-CODE-BRANCH', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '14', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'KOTA', 'menuseq' => '138', 'menuparent' => '17', 'menuicon' => 'FAS FA-CODE-BRANCH', 'aco_id' => '101', 'link' => '', 'menuexe' => '', 'menukode' => '138', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'MANDOR', 'menuseq' => '139', 'menuparent' => '17', 'menuicon' => 'FAS FA-CODE-BRANCH', 'aco_id' => '105', 'link' => '', 'menuexe' => '', 'menukode' => '139', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'MEKANIK', 'menuseq' => '140', 'menuparent' => '17', 'menuicon' => 'FAS FA-CODE-BRANCH', 'aco_id' => '109', 'link' => '', 'menuexe' => '', 'menukode' => '131', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'MERK', 'menuseq' => '144', 'menuparent' => '34', 'menuicon' => 'FAS FA-CODE-BRANCH', 'aco_id' => '113', 'link' => '', 'menuexe' => '', 'menukode' => '144', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'ORDERAN TRUCKING', 'menuseq' => '22', 'menuparent' => '15', 'menuicon' => 'FAS FA-CODE-BRANCH', 'aco_id' => '117', 'link' => '', 'menuexe' => '', 'menukode' => '22', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'PELANGGAN', 'menuseq' => '173', 'menuparent' => '55', 'menuicon' => 'FAS FA-CODE-BRANCH', 'aco_id' => '121', 'link' => '', 'menuexe' => '', 'menukode' => '173', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'PENERIMA', 'menuseq' => '172', 'menuparent' => '55', 'menuicon' => 'FAS FA-USER-TAG', 'aco_id' => '125', 'link' => '', 'menuexe' => '', 'menukode' => '172', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'PENERIMAAN TRUCKING', 'menuseq' => '131', 'menuparent' => '17', 'menuicon' => 'FAS FA-USER-MINUS', 'aco_id' => '129', 'link' => '', 'menuexe' => '', 'menukode' => '13D', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'PENGELUARAN TRUCKING', 'menuseq' => '1311', 'menuparent' => '17', 'menuicon' => 'FAS FA-USER-MINUS', 'aco_id' => '133', 'link' => '', 'menuexe' => '', 'menukode' => '13A', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'PROSES ABSENSI SUPIR', 'menuseq' => '23', 'menuparent' => '15', 'menuicon' => 'FAS FA-USER-TAG', 'aco_id' => '137', 'link' => '', 'menuexe' => '', 'menukode' => '23', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'RITASI', 'menuseq' => '24', 'menuparent' => '15', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '141', 'link' => '', 'menuexe' => '', 'menukode' => '24', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'SATUAN', 'menuseq' => '145', 'menuparent' => '34', 'menuicon' => 'FAS FA-USER', 'aco_id' => '145', 'link' => '', 'menuexe' => '', 'menukode' => '145', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'SURAT PENGANTAR', 'menuseq' => '25', 'menuparent' => '15', 'menuicon' => 'FAS FA-USER', 'aco_id' => '149', 'link' => '', 'menuexe' => '', 'menukode' => '25', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'SUPPLIER', 'menuseq' => '171', 'menuparent' => '55', 'menuicon' => 'FAS FA-USER', 'aco_id' => '153', 'link' => '', 'menuexe' => '', 'menukode' => '171', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'SUPIR', 'menuseq' => '1312', 'menuparent' => '17', 'menuicon' => 'FAS FA-USER', 'aco_id' => '157', 'link' => '', 'menuexe' => '', 'menukode' => '13B', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'SUB KELOMPOK', 'menuseq' => '100', 'menuparent' => '34', 'menuicon' => 'FAS FA-USER-TAG', 'aco_id' => '161', 'link' => '', 'menuexe' => '', 'menukode' => '146', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'STATUS CONTAINER', 'menuseq' => '100', 'menuparent' => '17', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '165', 'link' => '', 'menuexe' => '', 'menukode' => '13C', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'TARIF', 'menuseq' => '100', 'menuparent' => '17', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '169', 'link' => '', 'menuexe' => '', 'menukode' => '13E', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'JURNAL UMUM', 'menuseq' => '15', 'menuparent' => '3', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '15', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'KAS/BANK', 'menuseq' => '16', 'menuparent' => '3', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '16', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'RELASI', 'menuseq' => '17', 'menuparent' => '3', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '17', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'TRADO', 'menuseq' => '100', 'menuparent' => '17', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '173', 'link' => '', 'menuexe' => '', 'menukode' => '13F', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'UPAH SUPIR', 'menuseq' => '100', 'menuparent' => '17', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '177', 'link' => '', 'menuexe' => '', 'menukode' => '13G', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'UPAH RITASI', 'menuseq' => '100', 'menuparent' => '17', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '181', 'link' => '', 'menuexe' => '', 'menukode' => '13H', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'ZONA', 'menuseq' => '100', 'menuparent' => '17', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '185', 'link' => '', 'menuexe' => '', 'menukode' => '13I', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'TRANS .GL', 'menuseq' => '4', 'menuparent' => '0', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '4', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'JURNAL UMUM', 'menuseq' => '100', 'menuparent' => '60', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '189', 'link' => '', 'menuexe' => '', 'menukode' => '51', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'PENERIMAAN KAS/BANK', 'menuseq' => '100', 'menuparent' => '29', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '194', 'link' => '', 'menuexe' => '', 'menukode' => '32', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'TRANS. HUTANG/PIUTANG', 'menuseq' => '5', 'menuparent' => '0', 'menuicon' => 'FAS FA-MONEY-CHECK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '5', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'PIUTANG', 'menuseq' => '233', 'menuparent' => '63', 'menuicon' => 'FAS FA-MONEY-CHECK', 'aco_id' => '198', 'link' => '', 'menuexe' => '', 'menukode' => '61', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'PELUNASAN PIUTANG', 'menuseq' => '234', 'menuparent' => '63', 'menuicon' => 'FAS FA-MONEY-CHECK', 'aco_id' => '202', 'link' => '', 'menuexe' => '', 'menukode' => '62', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'PENGELUARAN TRUCKING', 'menuseq' => '655', 'menuparent' => '15', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '206', 'link' => '', 'menuexe' => '', 'menukode' => '26', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'SERVICE IN', 'menuseq' => '100', 'menuparent' => '15', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '210', 'link' => '', 'menuexe' => '', 'menukode' => '27', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'SERVICE OUT', 'menuseq' => '111', 'menuparent' => '15', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '214', 'link' => '', 'menuexe' => '', 'menukode' => '28', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'PENERIMAAN STOK', 'menuseq' => '100', 'menuparent' => '34', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '218', 'link' => '', 'menuexe' => '', 'menukode' => '147', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'PENGELUARAN STOK', 'menuseq' => '100', 'menuparent' => '34', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '222', 'link' => '', 'menuexe' => '', 'menukode' => '148', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'RINCIAN GAJI SUPIR', 'menuseq' => '100', 'menuparent' => '15', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '226', 'link' => '', 'menuexe' => '', 'menukode' => '29', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'PROSES GAJI SUPIR', 'menuseq' => '1', 'menuparent' => '15', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '230', 'link' => '', 'menuexe' => '', 'menukode' => '2A', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'ABSENSI SUPIR APPROVAL', 'menuseq' => '100', 'menuparent' => '15', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '234', 'link' => '', 'menuexe' => '', 'menukode' => '2B', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'HUTANG', 'menuseq' => '100', 'menuparent' => '63', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '238', 'link' => '', 'menuexe' => '', 'menukode' => '63', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'PEMBAYARAN HUTANG', 'menuseq' => '100', 'menuparent' => '63', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '242', 'link' => '', 'menuexe' => '', 'menukode' => '64', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'INVOICE', 'menuseq' => '100', 'menuparent' => '15', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '246', 'link' => '', 'menuexe' => '', 'menukode' => '2C', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'NOTA DEBET', 'menuseq' => '100', 'menuparent' => '63', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '250', 'link' => '', 'menuexe' => '', 'menukode' => '65', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'NOTA KREDIT', 'menuseq' => '100', 'menuparent' => '63', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '254', 'link' => '', 'menuexe' => '', 'menukode' => '66', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'PENERIMAAN TRUCKING', 'menuseq' => '100', 'menuparent' => '17', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '259', 'link' => '', 'menuexe' => '', 'menukode' => '13J', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'PENERIMAAN TRUCKING', 'menuseq' => '100', 'menuparent' => '15', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '263', 'link' => '', 'menuexe' => '', 'menukode' => '2D', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'STOK TRUCKING', 'menuseq' => '100', 'menuparent' => '0', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '6', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'PENERIMAAN  STOK', 'menuseq' => '100', 'menuparent' => '81', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '267', 'link' => '', 'menuexe' => '', 'menukode' => '71', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'PENGELUARAN STOK', 'menuseq' => '100', 'menuparent' => '81', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '271', 'link' => '', 'menuexe' => '', 'menukode' => '72', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'PENGELUARAN KAS BANK', 'menuseq' => '100', 'menuparent' => '29', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '275', 'link' => '', 'menuexe' => '', 'menukode' => '33', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'PENGEMBALIAN KAS GANTUNG', 'menuseq' => '100', 'menuparent' => '29', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '279', 'link' => '', 'menuexe' => '', 'menukode' => '34', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'REKAP PENGELUARAN KAS/BANK', 'menuseq' => '100', 'menuparent' => '29', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '283', 'link' => '', 'menuexe' => '', 'menukode' => '35', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'REKAP PENERIMAAN KAS/BANK', 'menuseq' => '100', 'menuparent' => '29', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '288', 'link' => '', 'menuexe' => '', 'menukode' => '36', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'HARI LIBUR', 'menuseq' => '100', 'menuparent' => '13', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '293', 'link' => '', 'menuexe' => '', 'menukode' => '123', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'PENERIMAAN GIRO', 'menuseq' => '100', 'menuparent' => '29', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '297', 'link' => '', 'menuexe' => '', 'menukode' => '37', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'LOG TRAIL', 'menuseq' => '100', 'menuparent' => '4', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '301', 'link' => '', 'menuexe' => '', 'menukode' => '119', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'INVOICE EXTRA', 'menuseq' => '100', 'menuparent' => '15', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '302', 'link' => '', 'menuexe' => '', 'menukode' => '2E', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'APPROVED JURNAL', 'menuseq' => '100', 'menuparent' => '60', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '306', 'link' => '', 'menuexe' => '', 'menukode' => '52', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'PAID & APPROVED', 'menuseq' => '100', 'menuparent' => '0', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '7', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'LAPORAN', 'menuseq' => '100', 'menuparent' => '0', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '0', 'link' => '', 'menuexe' => '', 'menukode' => '8', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'APPROVED KAS/BANK', 'menuseq' => '100', 'menuparent' => '93', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '309', 'link' => '', 'menuexe' => '', 'menukode' => '71', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'APPROVED HUTANG', 'menuseq' => '100', 'menuparent' => '93', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '311', 'link' => '', 'menuexe' => '', 'menukode' => '72', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'APPROVED NOTA', 'menuseq' => '100', 'menuparent' => '93', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '313', 'link' => '', 'menuexe' => '', 'menukode' => '73', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'APPROVED INVOICE', 'menuseq' => '100', 'menuparent' => '93', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '315', 'link' => '', 'menuexe' => '', 'menukode' => '74', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'APPROVAL PENDAPATAN', 'menuseq' => '100', 'menuparent' => '93', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '317', 'link' => '', 'menuexe' => '', 'menukode' => '75', 'modifiedby' => 'ADMIN',]);
        menu::create(['menuname' => 'PENDAPATAN SUPIR', 'menuseq' => '100', 'menuparent' => '93', 'menuicon' => 'FAS FA-TRUCK', 'aco_id' => '319', 'link' => '', 'menuexe' => '', 'menukode' => '76', 'modifiedby' => 'ADMIN',]);
    }
}
