<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TypeAkuntansi;
use Illuminate\Support\Facades\DB;

class TypeAkuntansiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete TypeAkuntansi");
        DB::statement("DBCC CHECKIDENT ('TypeAkuntansi', RESEED, 1);");

        typeakuntansi::create(['kodetype' => 'ADJUST', 'order' => '9110', 'keterangantype' => 'AKUN ADJUSTMENT', 'akuntansi_id' => '4', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
        typeakuntansi::create(['kodetype' => 'AKTIVA LAINNYA', 'order' => '1410', 'keterangantype' => 'AKTIVA LAIN-LAIN', 'akuntansi_id' => '1', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
        typeakuntansi::create(['kodetype' => 'AKTIVA LANCAR LAINNYA', 'order' => '1190', 'keterangantype' => 'AKTIVA LANCAR - LAINNYA', 'akuntansi_id' => '1', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
        typeakuntansi::create(['kodetype' => 'AKTIVA TAK BERWUJUD', 'order' => '1220', 'keterangantype' => 'AKTIVA TETAP TAK BERWUJUD', 'akuntansi_id' => '1', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
        typeakuntansi::create(['kodetype' => 'AKTIVA TETAP', 'order' => '1210', 'keterangantype' => 'AKTIVA TETAP', 'akuntansi_id' => '1', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
        typeakuntansi::create(['kodetype' => 'BANK', 'order' => '1111', 'keterangantype' => 'AKTIVA LANCAR - BANK', 'akuntansi_id' => '1', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
        typeakuntansi::create(['kodetype' => 'BARANG DALAM PERJALANAN', 'order' => '1150', 'keterangantype' => 'AKTIVA LANCAR - BARANG DALAM PERJALANAN', 'akuntansi_id' => '1', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
        typeakuntansi::create(['kodetype' => 'BEBAN LAIN - LAIN', 'order' => '6210', 'keterangantype' => 'BIAYA LAINNYA', 'akuntansi_id' => '4', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
        typeakuntansi::create(['kodetype' => 'BEBAN PEMBELIAN', 'order' => '5230', 'keterangantype' => 'BIAYA OPERASI PEMBELIAN', 'akuntansi_id' => '4', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
        typeakuntansi::create(['kodetype' => 'BEBAN PENJUALAN', 'order' => '5210', 'keterangantype' => 'BIAYA OPERASI PENJUALAN', 'akuntansi_id' => '4', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
        typeakuntansi::create(['kodetype' => 'BEBAN UMUM DAN ADMINISTRASI', 'order' => '5220', 'keterangantype' => 'BIAYA OPERASI UMUM', 'akuntansi_id' => '4', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
        typeakuntansi::create(['kodetype' => 'HARGA POKOK', 'order' => '5111', 'keterangantype' => 'HARGA POKOK', 'akuntansi_id' => '4', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
        typeakuntansi::create(['kodetype' => 'HUTANG JANGKA PANJANG', 'order' => '2210', 'keterangantype' => 'HUTANG JANGKA PANJANG', 'akuntansi_id' => '2', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
        typeakuntansi::create(['kodetype' => 'HUTANG LAINNYA', 'order' => '2310', 'keterangantype' => 'HUTANG LAIN-LAIN', 'akuntansi_id' => '2', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
        typeakuntansi::create(['kodetype' => 'HUTANG LANCAR LAINNYA', 'order' => '2119', 'keterangantype' => 'HUTANG LANCAR LAINNYA', 'akuntansi_id' => '2', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
        typeakuntansi::create(['kodetype' => 'INVESTASI', 'order' => '1310', 'keterangantype' => 'INVESTASI', 'akuntansi_id' => '1', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
        typeakuntansi::create(['kodetype' => 'KAS', 'order' => '1110', 'keterangantype' => 'AKTIVA LANCAR - KAS', 'akuntansi_id' => '1', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
        typeakuntansi::create(['kodetype' => 'LABA RUGI', 'order' => '3210', 'keterangantype' => 'LABA RUGI', 'akuntansi_id' => '4', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
        typeakuntansi::create(['kodetype' => 'MODAL', 'order' => '3110', 'keterangantype' => 'MODAL', 'akuntansi_id' => '3', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
        typeakuntansi::create(['kodetype' => 'PEMBELIAN', 'order' => '5110', 'keterangantype' => 'PEMBELIAN', 'akuntansi_id' => '4', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
        typeakuntansi::create(['kodetype' => 'PENDAPATAN LAIN - LAIN', 'order' => '6110', 'keterangantype' => 'PENDAPATAN LAIN-LAIN', 'akuntansi_id' => '4', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
        typeakuntansi::create(['kodetype' => 'PENJUALAN', 'order' => '4110', 'keterangantype' => 'PENJUALAN', 'akuntansi_id' => '4', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
        typeakuntansi::create(['kodetype' => 'PERKIRAAN HUTANG DAGANG', 'order' => '2110', 'keterangantype' => 'HUTANG DAGANG', 'akuntansi_id' => '2', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
        typeakuntansi::create(['kodetype' => 'PIUTANG DAGANG', 'order' => '1120', 'keterangantype' => 'AKTIVA LANCAR - PIUTANG DAGANG', 'akuntansi_id' => '1', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
        typeakuntansi::create(['kodetype' => 'PIUTANG LANCAR LAINNYA', 'order' => '1129', 'keterangantype' => 'AKTIVA LANCAR - PIUTANG LAINNYA', 'akuntansi_id' => '1', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
        typeakuntansi::create(['kodetype' => 'POTONGAN PEMBELIAN', 'order' => '5112', 'keterangantype' => 'POTONGAN PEMBELIAN', 'akuntansi_id' => '4', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
        typeakuntansi::create(['kodetype' => 'POTONGAN PENJUALAN', 'order' => '4112', 'keterangantype' => 'POTONGAN PENJUALAN', 'akuntansi_id' => '4', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
        typeakuntansi::create(['kodetype' => 'PPH', 'order' => '7110', 'keterangantype' => 'PAJAK PENGHASILAN PASAL 25', 'akuntansi_id' => '4', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
        typeakuntansi::create(['kodetype' => 'PRABAYAR (AKTIVA LANCAR)', 'order' => '1140', 'keterangantype' => 'AKTIVA LANCAR - UANG DIBAYAR DIMUKA', 'akuntansi_id' => '1', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
        typeakuntansi::create(['kodetype' => 'STOK', 'order' => '1130', 'keterangantype' => 'AKTIVA LANCAR - PERSEDIAAN BARANG', 'akuntansi_id' => '1', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
    }
}
