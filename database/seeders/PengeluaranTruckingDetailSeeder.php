<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PengeluaranTruckingDetail;
use Illuminate\Support\Facades\DB;

class PengeluaranTruckingDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete pengeluarantruckingdetail");
        DB::statement("DBCC CHECKIDENT ('pengeluarantruckingdetail', RESEED, 1);");


        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '1', 'nobukti' => 'PJT 0001/II/2023', 'supir_id' => '60', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '19645', 'keterangan' => 'GAJI MINUS SUPIR CHANDRA BK 8743 BU TGL. 01 FEBRUARI 2023', 'modifiedby' => 'ADMIN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '2', 'nobukti' => 'PJT 0002/II/2023', 'supir_id' => '83', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '100780', 'keterangan' => 'GAJI MINUS SUPIR ERIKSON BK 8264 FB TGL. 01 FEBRUARI 2023', 'modifiedby' => 'ADMIN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '3', 'nobukti' => 'PJT 0003/II/2023', 'supir_id' => '267', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '33408', 'keterangan' => 'GAJI MINUS SUPIR SAHBUDIN  BK 8178 EW TGL. 01 FEBRUARI 2023', 'modifiedby' => 'ADMIN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '4', 'nobukti' => 'PJT 0004/II/2023', 'supir_id' => '298', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '215736', 'keterangan' => 'GAJI MINUS SUPIR SULAIMAN B 9668 QZ TGL. 01 FEBRUARI 2023', 'modifiedby' => 'ADMIN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '5', 'nobukti' => 'PJT 0017/I/2023', 'supir_id' => '164', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '20948', 'keterangan' => '(KAMAL)GAJI MINUS SUPIR KAMAL BK 8050 CJ TGL. 24 JANUARI 2023', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '6', 'nobukti' => 'PJT 0018/III/2022', 'supir_id' => '172', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '479115', 'keterangan' => '(LIBERTO)TAGIH SUPIR LIBERTO ATAS BIAYA TEMBUS CLOSING SHIPPER USAHA RAJIN ', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '7', 'nobukti' => 'PJT 0039/VIII/2022', 'supir_id' => '215', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '11611000', 'keterangan' => '(NG BIN SAN)PINJAMAN BP. ASAN UNTUK BIAYA DEMURAGE PERIODE DESEMBER 2021 - 05 JULI 2022 SEBESAR RP. 11.611.000 ( DIPOTONG DARI FEE RP. 1.000.000 / BLN, DIMULAI SESUDAH PINJAMAN YNG LAMA HABIS DIPOTONG )', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '8', 'nobukti' => 'PJT 0043/VI/2022', 'supir_id' => '215', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '12910000', 'keterangan' => '(NG BIN SAN)PINJAMAN BP. ASAN UNTUK BIAYA DEMURAGE PERIODE DESEMBER 2021 - 31 MEI 2022 SEBESAR RP. 19.910.000 ( DIPOTONG DARI FEE RP. 1.000.000 / BLN, MULAI BLN JUNI 2022 )', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '9', 'nobukti' => 'PJT 0008/I/2023', 'supir_id' => '244', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '600304', 'keterangan' => '(RAMADANI GINTING)PINJAMAN SUPIR RAMADANI ATAS BIAYA ISI BBM TRADO TGL. 10 JANUARI 2023 UNTUK PROSES HUTANG BBM YANG BELAKANGAN DIISI UNTUK TGL. 23 DESEMBER 2022 RIC 0133/XII/2022', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '10', 'nobukti' => 'PJT 0040/XII/2022', 'supir_id' => '244', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '1087587', 'keterangan' => '(RAMADANI GINTING)GAJI MINUS SUPIR RAMADANI BK 8747 BU TGL. 21 DESEMBER 2022', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '11', 'nobukti' => 'PJT 0020/I/2023', 'supir_id' => '298', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '400000', 'keterangan' => '(SULAIMAN)BIAYA ISI BBM TRADO TGL. 19 JANUARI 2023 UNTUK PROSES HUTANG BBM A/N SULAIMAN  ( BAYAR CASH KARENA SPBU PT. RONATAMA AGRO MIGAS  BBM KOSONG ), DIJADIKAN PJT KARENA ADA KESALAHAN TIDAK TERINPUT DI RIC 0098/I/2023', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '12', 'nobukti' => 'PJT 0015/XII/2022', 'supir_id' => '71', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '300000', 'keterangan' => '(DEWANTO PURBA (EX)) PINJAMAN SUPIR DEWANTO ATAS DENDA UNTUK ABSEN TIDAK MASUK KERJA BULAN NOVEMBER 2022 ( TGL. 03, 15 OFF, TGL. 16,17,18,25,28,29,30 @ RP. 100.000, TGL. 19,26 @ RP. 200.000, TGL. 06,07 DESEMBER 2022 )', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '13', 'nobukti' => 'PJT 0001/XII/2022', 'supir_id' => '73', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '239750', 'keterangan' => '(DIMAS AGUNG PRABOWO)KLAIM SUPIR DIMAS AGUNG B 9120 QZ ATAS PEMBELIAN SPAREPART UNTUK SISIP MOBIL YANG KECELAKAAN  SEBESAR RP.436.000 (SPK 0002/XI/2022)', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '14', 'nobukti' => 'PJT 0004/VIII/2022', 'supir_id' => '73', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '102050', 'keterangan' => '(DIMAS AGUNG PRABOWO)CHARGE SUPIR DIMAS AGUNG ATAS BAN MASAK YG MELEDAK PADA GANDENGAN T-70 PENDEK DGN NO 1100 - 05003307 KETEBALAN 5MM SEBESAR RP.525.833 + RP.500.000 (BIAYA VUL 2)  TOTAL KESELURUHAN RP. 1.025.833 DIBULA', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '15', 'nobukti' => 'PJT 0037/X/2022', 'supir_id' => '73', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '555250', 'keterangan' => '(DIMAS AGUNG PRABOWO)PINJAMAN SUPIR DIMAS AGUNG ATAS BIAYA PERBAIKAN DI GUDANG PT. MSI KARENA NABRAK PEMBATAS ', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '16', 'nobukti' => 'PJT 0049/XII/2022', 'supir_id' => '73', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '80750', 'keterangan' => '(DIMAS AGUNG PRABOWO)KLAIM SUPIR DIMAS AGUNG ATAS LAMPU STOP YG HILANG DAN KACA SPION PETAK BESAR YG PECAH DIBUAT SUPIR SEBESAR RP.162.000 (SPK 0087/XII/2022)', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '17', 'nobukti' => 'PJT 0021/XII/2022', 'supir_id' => '146', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '675800', 'keterangan' => '(JANTO)CHARGE SUPIR JANTO ATAS BAN MASAK YG RUSAK/LEPAS BUNGA PADA B 9508 PH DGN NO BAN 1100  06415111 KETEBALAN 5MM SEBESAR RP.525.833 + RP.500.000 (BIAYA VUL 2), TOTAL KESELURUHAN RP. 1.025.833 DIBULATKAN', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '20', 'nobukti' => 'PJT 0022/VII/2022', 'supir_id' => '172', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '839615', 'keterangan' => '(LIBERTO)CHARGE SUPIR LIBERTO ATAS BAN MASAK YG MELEDAK PADA GANDENGAN T-08 PANJANG DGN NO 1100 - 06301214 KETEBALAN 6MM SEBESAR RP.609.000 + RP.500.000 (BIAYA VUL 2)  TOTAL KESELURUHAN RP. 1.109.000 DENGAN PE', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '21', 'nobukti' => 'PJT 0023/VII/2022', 'supir_id' => '172', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '70615', 'keterangan' => '(LIBERTO)GANTI BAN DALAM KARENA BAN MELEDAK PADA GANDENGAN T-08 PANJANG SEBESAR RP.340.000 SUPIR LIBERTO', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '18', 'nobukti' => 'PJT 0006/IX/2015', 'supir_id' => '172', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '9439545', 'keterangan' => '(LIBERTO)POTONG PINJAMAN ATAS PELUNASAN BIAYA PENYEWAAN CRANE,PERBAIKAN WARUNG & TRADO BK 8596 LU AKIBAT KECELAKAAN LIBERTO', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '19', 'nobukti' => 'PJT 0014/I/2019', 'supir_id' => '172', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '46875', 'keterangan' => '(LIBERTO)BIAYA PERBAIKAN 2 UNIT LOSBAK YANG DI TABRAK TRADO 8596 LU SUPIR LIBERTO (CHARGE SUPIR LIBERTO)', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '22', 'nobukti' => 'PJT 0070/VIII/2019', 'supir_id' => '172', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '1838994', 'keterangan' => '(LIBERTO)GANTI PERBAIKAN POMPA PUM SEBESAR RP.3.565.000,-  AKIBAT KELALAIAN SUPIR LIBERTO (BK 8596 LU)', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '23', 'nobukti' => 'PJT 0036/XII/2022', 'supir_id' => '244', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '244484', 'keterangan' => '(RAMADANI GINTING)KLAIM SUPIR RAMADANI GINTING ATAS KACA KABIN BELAKANG YG PECAH DIBUAT SUPIR SEBESAR RP.250.000 (SPK 0071/XII/2022)', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '24', 'nobukti' => 'PJT 0044/XI/2022', 'supir_id' => '267', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '15502369', 'keterangan' => '(SAHBUDIN)SETOR KE PANCARAN JATINUSA ATAS BIAYA REPAIRING CONTAINER CAIU 4636498, TXGU 5303991 YANG JATUH DITABRAK OLEH SUPIR SAHBUDIN SEBESAR RP. 16.642.369,- ( DIJADIKAN PJT SUPIR )', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '74', 'nobukti' => 'PJT 0046/III/2019', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '205000', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS LOCK GANDENGAN YANG HILANG  PADA GANDENGAN T-33 PANJANG  SEBESAR RP.285.000,- DIBAGI 17 SUPI', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '75', 'nobukti' => 'PJT 0047/XI/2018', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '290000', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS BAN YANG MELEDAK PADA GANDENGAN T-30 PANJANG DGN NO BAN 1100 - 0820110-2 SEBESAR RP.300.000,- DIBAGI 16 SUPIR', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '76', 'nobukti' => 'PJT 0048/IX/2018', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '590000', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS BAN YANG RUSAK DENGAN NO BAN 1100 - 1906303 PADA GANDENGAN T-07 PANJANG SEBESAR RP.600.000,- DIBAGI 17 SUPIR', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '77', 'nobukti' => 'PJT 0048/XI/2018', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '490000', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS BAN YANG MELEDAK PADA GANDENGAN T-51 PANJANG DGN NO BAN 1100 - 04918304 SEBESAR RP.500.000,- DIBAGI 16 SUPIR', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '78', 'nobukti' => 'PJT 0049/IX/2018', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '690000', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS BAN YANG MELEDAK DGN NO BAN 1100 - 04918306 YANG HILANG PADA GANDENGAN T-32 PANJANG SEBESAR RP.700.000,- DIBAGI 18 SUPIR', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '79', 'nobukti' => 'PJT 0050/II/2019', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '380000', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS BAN YANG MELEDAK  PADA GANDENGAN T-72 PANJANG  SEBESAR RP.400.000,- DIBAGI 17 SUPIR', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '80', 'nobukti' => 'PJT 0052/IV/2019', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '508452', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS BAN YANG MELEDAK DGN NO BAN 1100 - 1013305  PADA GANDENGAN T-16 PANJANG SETEBAL 6MM SEBESAR RP.600.000,- DIBAGI 18 SUPI', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '81', 'nobukti' => 'PJT 0054/IX/2018', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '690000', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS BAN YANG MELEDAK DGN NO BAN 1100 - 1013312 YANG HILANG PADA GANDENGAN T-08 PANJANG SEBESAR RP.700.000,- DIBAGI 18 SUPIR', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '82', 'nobukti' => 'PJT 0055/IX/2018', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '790000', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS BAN YANG MELEDAK DGN NO BAN 1100 - 0210311 YANG HILANG PADA GANDENGAN T-62 PANJANG SEBESAR RP.800.000,- DIBAGI 18 SUPIR', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '83', 'nobukti' => 'PJT 0057/IV/2019', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '735000', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS BAN YANG MELEDAK DGN NO BAN 1100 - 04922301  PADA GANDENGAN T-31 PANJANG SETEBAL 8MM SEBESAR RP.800.000,- DIBAGI 18 SUPIR', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '71', 'nobukti' => 'PJT 0037/III/2019', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '320000', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS BAN YANG AFKIR DGN NO BAN 1100-0804106  PADA GANDENGAN T-57 PENDEK  SEBESAR RP.400.000,- DIBAGI 17 SUPIR', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '72', 'nobukti' => 'PJT 0037/IX/2018', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '590000', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS BAN YANG MELEDAK DENGAN NO BAN 1100 - 02105301 PADA GANDENGAN T-23 PANJANG SEBESAR RP.600.000,- DIBAGI 17 SUPIR', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '73', 'nobukti' => 'PJT 0038/XII/2018', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '400000', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS BAN YANG RUSAK PADA GANDENGAN T-25 PANJANG DGN NO BAN 1100 - 2015206  SEBESAR RP.400.000,- DIBAGI 15 SUPIR', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '55', 'nobukti' => 'PJT 0023/VII/2018', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '683922', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS BAN YANG MELEDAK DGN NO BAN 1100 - 0818307 PADA GANDENGAN T-46 PANJANG SEBESAR RP.700.000,- DIBAGI 17 SUPIR', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '54', 'nobukti' => 'PJT 0022/IV/2018', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '663922', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS BAN YANG MELEDAK PADA GANDENGAN T- 13 PANJANG DGN NO BAN 1100 - 2008207 SETEBAL 7MM SEBESAR RP.700.000,- DIBAGI 18 SUPIR', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '56', 'nobukti' => 'PJT 0026/I/2019', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '345000', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS BAN YANG MELEDAK PADA GANDENGAN T-64 PENDEK  SEBESAR RP.500.000,- DIBAGI 16 SUPIR', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '57', 'nobukti' => 'PJT 0026/IV/2018', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '383922', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS BAN YANG RUSAK/KOYAK BESAR PADA GANDENGAN T- 51 PANJANG DGN NO BAN 1100 - 0925207 SETEBAL 4MM SEBESAR RP.400.000,- DIBAGI 19 SUPIR', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '58', 'nobukti' => 'PJT 0027/I/2019', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '235000', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS LOCK GANDENGAN YANG HILANG PADA GANDENGAN T-43 PANJANG  SEBESAR RP.275.000,- DIBAGI 16 SUPIR', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '59', 'nobukti' => 'PJT 0028/II/2019', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '360000', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS BAN YANG MELEDAK  PADA GANDENGAN T-45 PANJANG  SEBESAR RP.400.000,- DIBAGI 17 SUPIR', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '60', 'nobukti' => 'PJT 0028/III/2018', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '563922', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS BAN YANG MELEDAK DGN NO BAN 1100 - 2003303 PADA GANDENGAN T- 01 PANJANG SEBESAR RP.600.000,- DIBAGI 17 SUPIR', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '61', 'nobukti' => 'PJT 0028/IV/2018', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '783922', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS BAN YANG MELEDAK PADA GANDENGAN T- 45 PANJANG DGN NO BAN 1100 - 2008114 SETEBAL 8MM SEBESAR RP.800.000,- DIBAGI 19 SUPIR', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '62', 'nobukti' => 'PJT 0029/IV/2018', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '783922', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS BAN YANG RUSAK/LEPAS BUNGA PADA GANDENGAN T- 45 PANJANG DGN NO BAN 1100 - 1908208 SETEBAL 8MM SEBESAR RP.800.000,- DIBAGI 19 SUPIR', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '63', 'nobukti' => 'PJT 0030/I/2019', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '1080000', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS BAN YANG MELEDAK PADA GANDENGAN T-30 PANJANG  SEBESAR RP.1.100.000,- DIBAGI 16 SUPI', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '64', 'nobukti' => 'PJT 0030/III/2018', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '763922', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS BAN YANG MELEDAK DGN NO BAN 1100 - 1019202 PADA GANDENGAN T- 53 PANJANG SEBESAR RP.800.000,- DIBAGI 17 SUPIR', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '65', 'nobukti' => 'PJT 0032/VII/2018', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '783922', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS BAN YANG MELEDAK DGN NO BAN 1100 - 06321311 PADA GANDENGAN T-18 PANJANG SEBESAR RP.800.000,- DIBAGI 18 SUPIR', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '66', 'nobukti' => 'PJT 0033/III/2018', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '463922', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS BAN YANG MELEDAK DGN NO BAN 1100 - 1017209 PADA GANDENGAN T- 60 PENDEK SEBESAR RP.500.000,- DIBAGI 17 SUPIR', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '67', 'nobukti' => 'PJT 0034/VIII/2018', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '258922', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS LOCK GANDENGAN YANG HILANG PADA GANDENGAN T-44 PANJANG SEBESAR RP.275.000,- DIBAGI 18 SUPIR', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '68', 'nobukti' => 'PJT 0034/XII/2018', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '275000', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS LOCK GANDENGAN YANG HILANG PADA GANDENGAN T-03 PANJANG  SEBESAR RP.275.000,- DIBAGI 15 SUPI', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '69', 'nobukti' => 'PJT 0035/I/2019', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '255000', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS LOCK GANDENGAN YANG HILANG PADA GANDENGAN T-37 PANJANG  SEBESAR RP.275.000,- DIBAGI 16 SUPI', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '70', 'nobukti' => 'PJT 0036/IX/2018', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '790000', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS BAN YANG MELEDAK DENGAN NO BAN 1100 - 04923201 PADA GANDENGAN T-39 PANJANG SEBESAR RP.800.000,- DIBAGI 17 SUPIR', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '28', 'nobukti' => 'PJT 0003/II/2019', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '460000', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS BAN YANG MELEDAK  PADA GANDENGAN T-22 PANJANG  SEBESAR RP.500.000,- DIBAGI 16 SUPIR', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '29', 'nobukti' => 'PJT 0003/VII/2018', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '783922', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS BAN YANG RUSAK PADA GANDENGAN T-36 PANJANG DGN NO BAN 1100 - 0910105 SEBESAR RP.800.000,- DIBAGI 17 SUPIR', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '30', 'nobukti' => 'PJT 0004/III/2019', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '225000', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS LOCK GANDENGAN YANG HILANG  PADA GANDENGAN T-33 PANJANG  SEBESAR RP.285.000,- DIBAGI 17 SUPIR', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '42', 'nobukti' => 'PJT 0015/III/2018', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '40000', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS LOCK GANDENGAN YANG HILANG PADA GANDENGAN T- 07 PANJANG SEBESAR RP.285.000,- DIBAGI 17 SUPIR', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '43', 'nobukti' => 'PJT 0015/III/2019', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '105000', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS LOCK GANDENGAN YANG HILANG  PADA GANDENGAN T-62 PANJANG  SEBESAR RP.285.000,- DIBAGI 17 SUPI', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '44', 'nobukti' => 'PJT 0015/IX/2018', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '583922', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS BAN YANG RUSAK DENGAN NO BAN 1100 - 0803214-1 PADA GANDENGAN T-51 PANJANG SEBESAR RP.600.000,- DIBAGI 18 SUPIR', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '45', 'nobukti' => 'PJT 0015/V/2018', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '238922', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS LOCK GANDENGAN YANG HILANG PADA GANDENGAN T- 24 PANJANG SEBESAR RP.275.000,- DIBAGI 18 SUPIR', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '46', 'nobukti' => 'PJT 0016/III/2018', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '112354', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS LOCK GANDENGAN YANG HILANG PADA GANDENGAN T- 07 PANJANG SEBESAR RP.285.000,- DIBAGI 17 SUPIR', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '47', 'nobukti' => 'PJT 0016/IV/2019', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '500000', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS BAN YANG MELEDAK PADA GANDENGAN T-46 PANJANG DGN NO BAN 1100 - 0210312  SEBESAR RP.600.000,- DIBAGI 17 SUPI', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '48', 'nobukti' => 'PJT 0016/VIII/2018', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '383922', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS BAN YANG RUSAK/LEPAS BUNGA DGN NO BAN 1100 - 0829209 PADA GANDENGAN T-48 PENDEK SEBESAR RP.400.000,- DIBAGI 18 SUPIR', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '49', 'nobukti' => 'PJT 0017/III/2018', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '688922', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS BAN YANG MELEDAK DGN NO BAN 1100 - 04924112 PADA GANDENGAN T- 07 PANJANG SEBESAR RP.740.000,- DIBAGI 17 SUPIR', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '50', 'nobukti' => 'PJT 0019/IV/2019', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '210000', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS LOCK GANDENGAN YANG HILANG  PADA GANDENGAN T-35 PANJANG  SEBESAR RP.270.000,- DIBAGI 17 SUPIR', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '51', 'nobukti' => 'PJT 0020/IV/2019', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '740000', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS BAN YANG MELEDAK DGN NO BAN 1100 - 06523105  PADA GANDENGAN T-01 PANJANG  SEBESAR RP.800.000,- DIBAGI 17 SUPIR', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '52', 'nobukti' => 'PJT 0020/XII/2018', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '490000', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS BAN YANG MELEDAK PADA GANDENGAN T-64 PENDEK DGN NO BAN 1100 - 02118305 SEBESAR RP.500.000,- DIBAGI 15 SUPIR', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '53', 'nobukti' => 'PJT 0021/XII/2018', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '590000', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS BAN YANG MELEDAK PADA GANDENGAN T-05 PANJANG DGN NO BAN 1100 - 1008313 SEBESAR RP.600.000,- DIBAGI 15 SUPIR', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '34', 'nobukti' => 'PJT 0007/X/2018', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '265000', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS LOCK GANDENGAN YANG HILANG PADA GANDENGAN T-67 PENDEK SEBESAR RP.275.000,- DIBAGI 17 SUPIR', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '35', 'nobukti' => 'PJT 0008/VI/2018', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '783922', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS BAN YANG RUSAK PADA GANDENGAN T-45 PANJANG DGN NO BAN 1100 - 1908205 SEBESAR RP.800.000,- DIBAGI 17 SUPIR', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '36', 'nobukti' => 'PJT 0009/III/2019', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '225000', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS LOCK GANDENGAN YANG HILANG  PADA GANDENGAN T-14 PANJANG  SEBESAR RP.285.000,- DIBAGI 17 SUPIR', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '37', 'nobukti' => 'PJT 0009/VII/2018', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '258922', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS LOCK GANDENGAN YANG HILANG PADA GANDENGAN T-27 PANJANG SEBESAR RP.275.000,- DIBAGI 17 SUPIR', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '38', 'nobukti' => 'PJT 0010/IV/2018', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '158922', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS LOCK GANDENGAN YANG HILANG PADA GANDENGAN T- 62PANJANG SEBESAR RP.275.000,- DIBAGI 18 SUPIR', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '39', 'nobukti' => 'PJT 0010/VII/2018', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '158922', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS LOCK GANDENGAN YANG HILANG PADA GANDENGAN T-46 PANJANG SEBESAR RP.275.000,- DIBAGI 17 SUPIR', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '40', 'nobukti' => 'PJT 0011/VII/2018', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '258922', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS LOCK GANDENGAN YANG HILANG PADA GANDENGAN T-46 PANJANG SEBESAR RP.275.000,- DIBAGI 17 SUPIR', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '41', 'nobukti' => 'PJT 0013/X/2018', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '590000', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS BAN YANG LEPAS BUNGA PADA GANDENGAN T-19 PANJANG DGN NO BAN 1100 - 06309303 SEBESAR RP.600.000,- DIBAGI 17 SUPIR', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '31', 'nobukti' => 'PJT 0005/IV/2018', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '228922', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS LOCK GANDENGAN YANG HILANG PADA GANDENGAN T- 21 PANJANG SEBESAR RP.285.000,- DIBAGI 17 SUPIR', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '32', 'nobukti' => 'PJT 0005/X/2018', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '265000', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS LOCK GANDENGAN YANG HILANG PADA GANDENGAN T-08 PANJANG SEBESAR RP.275.000,- DIBAGI 18 SUPIR', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '33', 'nobukti' => 'PJT 0006/III/2019', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '725000', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS BAN YANG MELEDAK  PADA GANDENGAN T-17 PANJANG  SEBESAR RP.805.000,- DIBAGI 17 SUPI', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '25', 'nobukti' => 'PJT 0001/VI/2018', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '663922', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS BAN YANG RUSAK PADA GANDENGAN T-16 PANJANG DGN NO BAN 1100 - 01020101 SEBESAR RP.700.000,- DIBAGI 18 SUPIR', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '26', 'nobukti' => 'PJT 0001/VIII/2018', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '463922', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS BAN YANG MELEDAK DGN NO BAN 1100 - 2016207 PADA GANDENGAN T-34 PANJANG SEBESAR RP.500.000,- DIBAGI 18 SUPIR', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '27', 'nobukti' => 'PJT 0001/XI/2018', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '490000', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS BAN YANG MELEDAK PADA GANDENGAN T-28 PENDEK DGN NO BAN 1100 - 02118214 SEBESAR RP.500.000,- DIBAGI 16 SUPIR', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '85', 'nobukti' => 'PJT 0080/IV/2019', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '720000', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS BAN YANG MELEDAK DGN NO BAN 1100 - 04918310  PADA GANDENGAN T-18 PANJANG SETEBAL 8MM SEBESAR RP.800.000,- DIBAGI 18 SUPI', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '86', 'nobukti' => 'PJT 0081/IV/2019', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '740000', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS BAN YANG MELEDAK DGN NO BAN 1100 - 06315116  PADA GANDENGAN T-62 PANJANG SETEBAL 8MM SEBESAR RP.800.000,- DIBAGI 18 SUPI', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '84', 'nobukti' => 'PJT 0066/IV/2019', 'supir_id' => '0', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '660000', 'keterangan' => '(SEMUA)CHARGE BERSAMA SEMUA SUPIR ATAS BAN YANG MELEDAK DGN NO BAN 1100 - 01028301 PADA GANDENGAN T-16 PANJANG SETEBAL 7MM SEBESAR RP.700.000,- DIBAGI 17 SUPIR', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '87', 'nobukti' => 'PJT 0014/X/2022', 'supir_id' => '305', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '662000', 'keterangan' => '(SYAFRIZAL)CHARGE SUPIR SYAFRIZAL ATAS BAN MASAK YG MELEDAK PANJANG DGN NO 1100  06425101 KETEBALAN 6MM SEBESAR RP.661.999 + RP.500.000 (BIAYA VUL 2)  TOTAL KESELURUHAN RP. 1.161.999 DIBULATKAN MENJADI RP.1.162', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '88', 'nobukti' => 'PJT 0002/XII/2022', 'supir_id' => '307', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '665700', 'keterangan' => '(SYAIFUL BAHRI LUBIS)CHARGE SUPIR SYAIFUL ATAS BAN MASAK YG RUSAK JEBOL SAMPING PADA B 9949 JH DGN NO BAN 1100 - 06316109 KETEBALAN 4MM SEBESAR RP.420.666 + RP.500.000 (BIAYA VUL 2), TOTAL KESELURUHAN RP. 920.666 DIBULATK', 'modifiedby' => 'RYAN',]);
        pengeluarantruckingdetail::create(['pengeluarantruckingheader_id' => '89', 'nobukti' => 'PJT 0062/XI/2022', 'supir_id' => '307', 'penerimaantruckingheader_nobukti' => '', 'nominal' => '7182500', 'keterangan' => '(SYAIFUL BAHRI LUBIS)PINJAMAN SUPIR SYAIFUL B 9949 JH UNTUK BIAYA PERDAMAIAN, KEPOLISIAN DAN IBU SIDABUTAR ATAS LAKA DI TEBING TINGGI', 'modifiedby' => 'RYAN',]);
    }
}
