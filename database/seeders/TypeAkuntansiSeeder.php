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

        typeakuntansi::create(['kodetype' => 'ADJUST', 'Order' => '9110', 'keterangantype' => 'Akun Adjustment', 'akuntansi_id' => '4', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        typeakuntansi::create(['kodetype' => 'AKTIVA LAINNYA', 'Order' => '1410', 'keterangantype' => 'Aktiva Lain-Lain', 'akuntansi_id' => '1', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        typeakuntansi::create(['kodetype' => 'AKTIVA LANCAR LAINNYA', 'Order' => '1190', 'keterangantype' => 'Aktiva Lancar - Lainnya', 'akuntansi_id' => '1', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        typeakuntansi::create(['kodetype' => 'AKTIVA TAK BERWUJUD', 'Order' => '1220', 'keterangantype' => 'Aktiva Tetap Tak Berwujud', 'akuntansi_id' => '1', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        typeakuntansi::create(['kodetype' => 'AKTIVA TETAP', 'Order' => '1210', 'keterangantype' => 'Aktiva Tetap', 'akuntansi_id' => '1', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        typeakuntansi::create(['kodetype' => 'BANK', 'Order' => '1111', 'keterangantype' => 'Aktiva Lancar - Bank', 'akuntansi_id' => '1', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        typeakuntansi::create(['kodetype' => 'BARANG DALAM PERJALANAN', 'Order' => '1150', 'keterangantype' => 'Aktiva Lancar - Barang Dalam Perjalanan', 'akuntansi_id' => '1', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        typeakuntansi::create(['kodetype' => 'BEBAN LAIN - LAIN', 'Order' => '6210', 'keterangantype' => 'Biaya Lainnya', 'akuntansi_id' => '4', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        typeakuntansi::create(['kodetype' => 'BEBAN PEMBELIAN', 'Order' => '5230', 'keterangantype' => 'Biaya Operasi Pembelian', 'akuntansi_id' => '4', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        typeakuntansi::create(['kodetype' => 'BEBAN PENJUALAN', 'Order' => '5210', 'keterangantype' => 'Biaya Operasi Penjualan', 'akuntansi_id' => '4', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        typeakuntansi::create(['kodetype' => 'BEBAN UMUM DAN ADMINISTRASI', 'Order' => '5220', 'keterangantype' => 'Biaya Operasi Umum', 'akuntansi_id' => '4', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        typeakuntansi::create(['kodetype' => 'HARGA POKOK', 'Order' => '5111', 'keterangantype' => 'Harga Pokok', 'akuntansi_id' => '4', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        typeakuntansi::create(['kodetype' => 'HUTANG JANGKA PANJANG', 'Order' => '2210', 'keterangantype' => 'Hutang Jangka Panjang', 'akuntansi_id' => '2', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        typeakuntansi::create(['kodetype' => 'HUTANG LAINNYA', 'Order' => '2310', 'keterangantype' => 'Hutang Lain-lain', 'akuntansi_id' => '2', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        typeakuntansi::create(['kodetype' => 'HUTANG LANCAR LAINNYA', 'Order' => '2119', 'keterangantype' => 'Hutang Lancar Lainnya', 'akuntansi_id' => '2', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        typeakuntansi::create(['kodetype' => 'INVESTASI', 'Order' => '1310', 'keterangantype' => 'Investasi', 'akuntansi_id' => '1', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        typeakuntansi::create(['kodetype' => 'KAS', 'Order' => '1110', 'keterangantype' => 'Aktiva Lancar - Kas', 'akuntansi_id' => '1', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        typeakuntansi::create(['kodetype' => 'LABA RUGI', 'Order' => '3210', 'keterangantype' => 'Laba Rugi', 'akuntansi_id' => '4', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        typeakuntansi::create(['kodetype' => 'MODAL', 'Order' => '3110', 'keterangantype' => 'Modal', 'akuntansi_id' => '3', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        typeakuntansi::create(['kodetype' => 'PEMBELIAN', 'Order' => '5110', 'keterangantype' => 'Pembelian', 'akuntansi_id' => '4', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        typeakuntansi::create(['kodetype' => 'PENDAPATAN LAIN - LAIN', 'Order' => '6110', 'keterangantype' => 'Pendapatan lain-lain', 'akuntansi_id' => '4', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        typeakuntansi::create(['kodetype' => 'PENJUALAN', 'Order' => '4110', 'keterangantype' => 'Penjualan', 'akuntansi_id' => '4', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        typeakuntansi::create(['kodetype' => 'PERKIRAAN HUTANG DAGANG', 'Order' => '2110', 'keterangantype' => 'Hutang Dagang', 'akuntansi_id' => '2', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        typeakuntansi::create(['kodetype' => 'PIUTANG DAGANG', 'Order' => '1120', 'keterangantype' => 'Aktiva Lancar - Piutang Dagang', 'akuntansi_id' => '1', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        typeakuntansi::create(['kodetype' => 'PIUTANG LANCAR LAINNYA', 'Order' => '1129', 'keterangantype' => 'Aktiva Lancar - Piutang Lainnya', 'akuntansi_id' => '1', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        typeakuntansi::create(['kodetype' => 'POTONGAN PEMBELIAN', 'Order' => '5112', 'keterangantype' => 'Potongan Pembelian', 'akuntansi_id' => '4', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        typeakuntansi::create(['kodetype' => 'POTONGAN PENJUALAN', 'Order' => '4112', 'keterangantype' => 'Potongan Penjualan', 'akuntansi_id' => '4', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        typeakuntansi::create(['kodetype' => 'PPh', 'Order' => '7110', 'keterangantype' => 'Pajak Penghasilan Pasal 25', 'akuntansi_id' => '4', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        typeakuntansi::create(['kodetype' => 'PRABAYAR (AKTIVA LANCAR)', 'Order' => '1140', 'keterangantype' => 'Aktiva Lancar - Uang Dibayar Dimuka', 'akuntansi_id' => '1', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        typeakuntansi::create(['kodetype' => 'STOK', 'Order' => '1130', 'keterangantype' => 'Aktiva Lancar - Persediaan Barang', 'akuntansi_id' => '1', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
    }
}
