<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete Supplier");
        DB::statement("DBCC CHECKIDENT ('Supplier', RESEED, 1);");

        supplier::create(['statusapproval' => 4, 'namasupplier' => 'AC BUBUT', 'namakontak' => 'DARWIE LIEONO', 'alamat' => 'JL.KL YOS SUDARSO KM 14.8 NO.41 SEBELAH SPBU', 'kota' => 'MEDAN', 'kodepos' => '-', 'notelp1' => '061-6850161', 'notelp2' => '061-6858692', 'email' => '-', 'statusaktif' => '2', 'web' => '', 'namapemilik' => 'LIE WAN TJAI', 'jenisusaha' => 'BENGKEL', 'top' => '30', 'bank' => 'MAY BANK/BII', 'rekeningbank' => '2467001179', 'namarekening' => 'DARWIE LIEONO', 'jabatan' => 'DIREKTUR', 'statusdaftarharga' => '97', 'kategoriusaha' => 'PENGECER', 'modifiedby' => 'ADMIN',]);
        supplier::create(['statusapproval' => 4, 'namasupplier' => 'ACHUNG', 'namakontak' => '-', 'alamat' => '-', 'kota' => '-', 'kodepos' => '-', 'notelp1' => '-', 'notelp2' => '-', 'email' => '-', 'statusaktif' => '1', 'web' => '', 'namapemilik' => '-', 'jenisusaha' => '-', 'top' => '7', 'bank' => '-', 'rekeningbank' => '-', 'namarekening' => '-', 'jabatan' => '-', 'statusdaftarharga' => '97', 'kategoriusaha' => 'PENGECER', 'modifiedby' => 'ADMIN',]);
        supplier::create(['statusapproval' => 4, 'namasupplier' => 'ATB', 'namakontak' => 'JIMMY YAPPETER', 'alamat' => 'JL.TELUK HARU REL PJKA NO.58', 'kota' => 'MEDAN', 'kodepos' => '-', 'notelp1' => '061-6851187', 'notelp2' => '061-6851187', 'email' => 'JIMMYYAPPETER@GMAIL.COM', 'statusaktif' => '1', 'web' => '', 'namapemilik' => 'ENG SIONG', 'jenisusaha' => 'ISI ULANG OXIGEN,GAS LAIN,BESI', 'top' => '30', 'bank' => 'BCA', 'rekeningbank' => '2420838878', 'namarekening' => 'JIMMY YAPPETER', 'jabatan' => 'MANAGER', 'statusdaftarharga' => '96', 'kategoriusaha' => 'SUB-AGEN', 'modifiedby' => 'ADMIN',]);
        supplier::create(['statusapproval' => 4, 'namasupplier' => 'BAHAGIA BERSAMA', 'namakontak' => '', 'alamat' => '', 'kota' => '', 'kodepos' => '', 'notelp1' => '', 'notelp2' => '', 'email' => '', 'statusaktif' => '1', 'web' => '', 'namapemilik' => '', 'jenisusaha' => '', 'top' => '0', 'bank' => '', 'rekeningbank' => '', 'namarekening' => '', 'jabatan' => '', 'statusdaftarharga' => '97', 'kategoriusaha' => '', 'modifiedby' => 'ADMIN',]);
        supplier::create(['statusapproval' => 4, 'namasupplier' => 'BELAWAN INDAH', 'namakontak' => '', 'alamat' => '', 'kota' => '', 'kodepos' => '', 'notelp1' => '', 'notelp2' => '', 'email' => '', 'statusaktif' => '1', 'web' => '', 'namapemilik' => '', 'jenisusaha' => '', 'top' => '0', 'bank' => '', 'rekeningbank' => '', 'namarekening' => '', 'jabatan' => '', 'statusdaftarharga' => '97', 'kategoriusaha' => '', 'modifiedby' => 'ADMIN',]);
        supplier::create(['statusapproval' => 4, 'namasupplier' => 'BENGKEL ANDA', 'namakontak' => '-', 'alamat' => '-', 'kota' => '-', 'kodepos' => '-', 'notelp1' => '-', 'notelp2' => '-', 'email' => '-', 'statusaktif' => '1', 'web' => '-', 'namapemilik' => '-', 'jenisusaha' => '-', 'top' => '1', 'bank' => '-', 'rekeningbank' => '-', 'namarekening' => '-', 'jabatan' => '-', 'statusdaftarharga' => '96', 'kategoriusaha' => 'PRODUSEN', 'modifiedby' => 'ADMIN',]);
        supplier::create(['statusapproval' => 4, 'namasupplier' => 'BENGKEL LAS ISTIMEWA', 'namakontak' => '', 'alamat' => '', 'kota' => '', 'kodepos' => '', 'notelp1' => '', 'notelp2' => '', 'email' => '', 'statusaktif' => '1', 'web' => '', 'namapemilik' => '', 'jenisusaha' => '', 'top' => '0', 'bank' => '', 'rekeningbank' => '', 'namarekening' => '', 'jabatan' => '', 'statusdaftarharga' => '97', 'kategoriusaha' => '', 'modifiedby' => 'ADMIN',]);
        supplier::create(['statusapproval' => 4, 'namasupplier' => 'BENGKEL TURBO', 'namakontak' => '-', 'alamat' => '-', 'kota' => 'MEDAN', 'kodepos' => '-', 'notelp1' => '-', 'notelp2' => '-', 'email' => '-', 'statusaktif' => '1', 'web' => '-', 'namapemilik' => '-', 'jenisusaha' => '-', 'top' => '30', 'bank' => '-', 'rekeningbank' => '-', 'namarekening' => '-', 'jabatan' => '-', 'statusdaftarharga' => '96', 'kategoriusaha' => 'PRODUSEN', 'modifiedby' => 'ADMIN',]);
        supplier::create(['statusapproval' => 4, 'namasupplier' => 'BRAYAN JAYA', 'namakontak' => 'MEI MEI', 'alamat' => 'JLN. K.L. YOSSUDARSO NO.172-L PULO BRAYAN', 'kota' => 'MEDAN', 'kodepos' => '20116', 'notelp1' => '061 - 6619612', 'notelp2' => '061 - 6624771', 'email' => 'BRAYAN.JAYA@GMAIL.COM', 'statusaktif' => '1', 'web' => '-', 'namapemilik' => 'DARWIN KASLI', 'jenisusaha' => 'KONSTRUKSI BESI DAN BAHAN BANGUNAN', 'top' => '30', 'bank' => 'BCA', 'rekeningbank' => '242 - 0112800', 'namarekening' => 'DARWIN KASLI', 'jabatan' => 'BAGIAN KEUANGAN', 'statusdaftarharga' => '96', 'kategoriusaha' => 'DISTRIBUTOR', 'modifiedby' => 'ADMIN',]);
        supplier::create(['statusapproval' => 4, 'namasupplier' => 'BUBUT AC', 'namakontak' => 'PEMILIK', 'alamat' => 'JL.K.L YOSSUDARSO KM 14,8 NO.41 SEBELAH SPBU', 'kota' => 'MEDAN', 'kodepos' => '20251', 'notelp1' => '061-6850161', 'notelp2' => '-', 'email' => '-', 'statusaktif' => '1', 'web' => '', 'namapemilik' => 'LIE WAN TJAI', 'jenisusaha' => 'BENGKEL', 'top' => '30', 'bank' => 'BII / MAYBANK', 'rekeningbank' => '2467001179', 'namarekening' => 'DARWIE LIEONO / SIUE KIM', 'jabatan' => 'PEMILIK', 'statusdaftarharga' => '97', 'kategoriusaha' => 'PENGECER', 'modifiedby' => 'ADMIN',]);
        supplier::create(['statusapproval' => 4, 'namasupplier' => 'CAHAYA RADIATOR', 'namakontak' => '-', 'alamat' => 'JL.YOSSUDARSO SIMP.DARMIN', 'kota' => 'MEDAN', 'kodepos' => '-', 'notelp1' => '-', 'notelp2' => '-', 'email' => '-', 'statusaktif' => '1', 'web' => '', 'namapemilik' => 'CAHAYA', 'jenisusaha' => 'REPERASI ', 'top' => '7', 'bank' => '-', 'rekeningbank' => '-', 'namarekening' => '-', 'jabatan' => '-', 'statusdaftarharga' => '97', 'kategoriusaha' => 'SUB-AGEN', 'modifiedby' => 'ADMIN',]);
        supplier::create(['statusapproval' => 4, 'namasupplier' => 'CAMUS PRINTING', 'namakontak' => '', 'alamat' => '', 'kota' => '', 'kodepos' => '', 'notelp1' => '', 'notelp2' => '', 'email' => '', 'statusaktif' => '1', 'web' => '', 'namapemilik' => '', 'jenisusaha' => '', 'top' => '0', 'bank' => '', 'rekeningbank' => '', 'namarekening' => '', 'jabatan' => '', 'statusdaftarharga' => '97', 'kategoriusaha' => '', 'modifiedby' => 'ADMIN',]);
        supplier::create(['statusapproval' => 4, 'namasupplier' => 'CASH', 'namakontak' => '', 'alamat' => '', 'kota' => '', 'kodepos' => '', 'notelp1' => '', 'notelp2' => '', 'email' => '', 'statusaktif' => '1', 'web' => '', 'namapemilik' => '', 'jenisusaha' => '', 'top' => '7', 'bank' => '', 'rekeningbank' => '', 'namarekening' => '', 'jabatan' => '', 'statusdaftarharga' => '97', 'kategoriusaha' => '', 'modifiedby' => 'ADMIN',]);
        supplier::create(['statusapproval' => 4, 'namasupplier' => 'CASH SPB', 'namakontak' => '', 'alamat' => '', 'kota' => '', 'kodepos' => '', 'notelp1' => '', 'notelp2' => '', 'email' => '', 'statusaktif' => '1', 'web' => '', 'namapemilik' => '', 'jenisusaha' => '', 'top' => '7', 'bank' => '', 'rekeningbank' => '', 'namarekening' => '', 'jabatan' => '', 'statusdaftarharga' => '97', 'kategoriusaha' => '', 'modifiedby' => 'ADMIN',]);
        supplier::create(['statusapproval' => 4, 'namasupplier' => 'CIN HAN', 'namakontak' => 'HASAN', 'alamat' => 'JL.YOSSUDARSO KM 10,5', 'kota' => 'MEDAN', 'kodepos' => '-', 'notelp1' => '-', 'notelp2' => '-', 'email' => '-', 'statusaktif' => '1', 'web' => '', 'namapemilik' => 'HASAN', 'jenisusaha' => 'KERAJINAN TANGAN', 'top' => '7', 'bank' => 'CASH', 'rekeningbank' => '-', 'namarekening' => '-', 'jabatan' => 'PEMILIK', 'statusdaftarharga' => '96', 'kategoriusaha' => 'PRODUSEN', 'modifiedby' => 'ADMIN',]);
        supplier::create(['statusapproval' => 4, 'namasupplier' => 'CV. AUTO DINAMIKA MAKMUR', 'namakontak' => 'HESKEL TAMPUBOLON', 'alamat' => 'JL. ISMALIYAH NO.12 E', 'kota' => 'MEDAN', 'kodepos' => '20215', 'notelp1' => '0811648080', 'notelp2' => '-', 'email' => 'AUTO_DINAMIKA_MAKMUR@YAHOO.COM', 'statusaktif' => '1', 'web' => '-', 'namapemilik' => 'LEONARDO WINARDI', 'jenisusaha' => 'SUPLIER SUKU CADANG & ACCESORIS MOBIL', 'top' => '14', 'bank' => 'BCA', 'rekeningbank' => '3831788885', 'namarekening' => 'CV. AUTO DINAMIKA MAKMUR', 'jabatan' => 'MARKETING EXECUTIVE', 'statusdaftarharga' => '96', 'kategoriusaha' => 'DISTRIBUTOR', 'modifiedby' => 'ADMIN',]);
        supplier::create(['statusapproval' => 4, 'namasupplier' => 'CV. JAYA ABADI TRANS', 'namakontak' => 'BENDI', 'alamat' => 'JALAN PULAU SEBIRA NO.88 KIM', 'kota' => 'MEDAN', 'kodepos' => '-', 'notelp1' => '061 - 6871449', 'notelp2' => '061 - 6871486', 'email' => '-', 'statusaktif' => '1', 'web' => '-', 'namapemilik' => '-', 'jenisusaha' => 'JASA ANGKUTAN', 'top' => '7', 'bank' => 'BCA', 'rekeningbank' => '8195032188', 'namarekening' => 'CV. JAYA ABADI TRANS', 'jabatan' => 'MANDOR', 'statusdaftarharga' => '96', 'kategoriusaha' => 'SUB-AGEN', 'modifiedby' => 'ADMIN',]);
        supplier::create(['statusapproval' => 4, 'namasupplier' => 'CV. RODA MAS VULKANISIR BAN', 'namakontak' => 'EDI YOHAN', 'alamat' => 'JL.TITI PAHLAWAN ], PAYA PASIR KEC. MEDAN MARELAN NO.47', 'kota' => 'MEDAN', 'kodepos' => '20250', 'notelp1' => '081361666359', 'notelp2' => '085373558688', 'email' => 'JOHANCUTE1986@GMAIL.COM', 'statusaktif' => '1', 'web' => '-', 'namapemilik' => 'EDI YOHAN', 'jenisusaha' => 'JASA VULKANISIR', 'top' => '30', 'bank' => 'MESTIKA', 'rekeningbank' => '20106135928', 'namarekening' => 'EDI YOHAN', 'jabatan' => 'PEMILIK', 'statusdaftarharga' => '96', 'kategoriusaha' => 'PRODUSEN', 'modifiedby' => 'ADMIN',]);
        supplier::create(['statusapproval' => 4, 'namasupplier' => 'CV.PAJAR INDAH', 'namakontak' => '-', 'alamat' => '-', 'kota' => '-', 'kodepos' => '-', 'notelp1' => '-', 'notelp2' => '-', 'email' => '-', 'statusaktif' => '1', 'web' => '-', 'namapemilik' => '-', 'jenisusaha' => '-', 'top' => '1', 'bank' => '-', 'rekeningbank' => '-', 'namarekening' => '-', 'jabatan' => '-', 'statusdaftarharga' => '96', 'kategoriusaha' => 'PRODUSEN', 'modifiedby' => 'ADMIN',]);
        supplier::create(['statusapproval' => 4, 'namasupplier' => 'DAVID (MUSTIKA SAKTI)', 'namakontak' => '-', 'alamat' => 'JL. TANJUNG MULIA', 'kota' => 'MEDAN', 'kodepos' => '-', 'notelp1' => '-', 'notelp2' => '-', 'email' => '-', 'statusaktif' => '1', 'web' => '-', 'namapemilik' => '-', 'jenisusaha' => '-', 'top' => '1', 'bank' => '-', 'rekeningbank' => '-', 'namarekening' => '-', 'jabatan' => '-', 'statusdaftarharga' => '96', 'kategoriusaha' => 'DISTRIBUTOR', 'modifiedby' => 'ADMIN',]);
        supplier::create(['statusapproval' => 4, 'namasupplier' => 'DIESEL JAYA (DJ)', 'namakontak' => '-', 'alamat' => '-', 'kota' => '-', 'kodepos' => '-', 'notelp1' => '-', 'notelp2' => '-', 'email' => '-', 'statusaktif' => '1', 'web' => '-', 'namapemilik' => '-', 'jenisusaha' => '-', 'top' => '30', 'bank' => '-', 'rekeningbank' => '-', 'namarekening' => '-', 'jabatan' => '-', 'statusdaftarharga' => '96', 'kategoriusaha' => 'DISTRIBUTOR', 'modifiedby' => 'ADMIN',]);
        supplier::create(['statusapproval' => 4, 'namasupplier' => 'GENUINE MAKMUR', 'namakontak' => '', 'alamat' => '', 'kota' => '', 'kodepos' => '', 'notelp1' => '', 'notelp2' => '', 'email' => '', 'statusaktif' => '1', 'web' => '', 'namapemilik' => '', 'jenisusaha' => '', 'top' => '0', 'bank' => '', 'rekeningbank' => '', 'namarekening' => '', 'jabatan' => '', 'statusdaftarharga' => '97', 'kategoriusaha' => '', 'modifiedby' => 'ADMIN',]);
        supplier::create(['statusapproval' => 4, 'namasupplier' => 'GUNAWAN', 'namakontak' => '', 'alamat' => 'JL.MESJID KESAWAN SQUARE', 'kota' => '', 'kodepos' => '', 'notelp1' => '4552406', 'notelp2' => '', 'email' => '', 'statusaktif' => '1', 'web' => '', 'namapemilik' => '', 'jenisusaha' => '', 'top' => '0', 'bank' => '', 'rekeningbank' => '', 'namarekening' => '', 'jabatan' => '', 'statusdaftarharga' => '97', 'kategoriusaha' => '', 'modifiedby' => 'ADMIN',]);
        supplier::create(['statusapproval' => 4, 'namasupplier' => 'HARAPAN JAYA', 'namakontak' => 'TOKO', 'alamat' => 'JL. K.L. YOS SUDARSO - SIMP KANTOR', 'kota' => 'MEDAN', 'kodepos' => '-', 'notelp1' => '061 - 6850964', 'notelp2' => '-', 'email' => '-', 'statusaktif' => '1', 'web' => '-', 'namapemilik' => '-', 'jenisusaha' => '-', 'top' => '1', 'bank' => '-', 'rekeningbank' => '-', 'namarekening' => '-', 'jabatan' => '-', 'statusdaftarharga' => '96', 'kategoriusaha' => 'PENGECER', 'modifiedby' => 'ADMIN',]);
        supplier::create(['statusapproval' => 4, 'namasupplier' => 'HOKI JAYA', 'namakontak' => 'ASIONG', 'alamat' => 'JL.MARELAN RAYA PASAR IV NO.58', 'kota' => 'MEDAN', 'kodepos' => '-', 'notelp1' => '0811659404', 'notelp2' => '061-6855537', 'email' => 'ASIONGMIMIE@GMAIL.COM', 'statusaktif' => '1', 'web' => '', 'namapemilik' => 'KARTONO', 'jenisusaha' => 'SPAREPART MOBIL', 'top' => '30', 'bank' => 'BCA', 'rekeningbank' => '2420781833/10120000299', 'namarekening' => 'MIMI', 'jabatan' => 'PEMIMPIN', 'statusdaftarharga' => '96', 'kategoriusaha' => 'DISTRIBUTOR', 'modifiedby' => 'ADMIN',]);
        supplier::create(['statusapproval' => 4, 'namasupplier' => 'HOKI JAYA (EHT)', 'namakontak' => 'ASIONG', 'alamat' => 'JL. MARELAN RAYA PASR IV NO. 58', 'kota' => 'MEDAN', 'kodepos' => '-', 'notelp1' => '0811659404', 'notelp2' => '061-6855537', 'email' => 'ASIONGMIMIE@GMAIL.COM', 'statusaktif' => '1', 'web' => '-', 'namapemilik' => 'KARTONO', 'jenisusaha' => 'SPAREPART MOBIL', 'top' => '30', 'bank' => 'BCA', 'rekeningbank' => '2420781833/10120000299', 'namarekening' => 'MIMI', 'jabatan' => 'PEMIMPIN', 'statusdaftarharga' => '96', 'kategoriusaha' => 'DISTRIBUTOR', 'modifiedby' => 'ADMIN',]);
        supplier::create(['statusapproval' => 4, 'namasupplier' => 'INFOMEDIA', 'namakontak' => '-', 'alamat' => '-', 'kota' => '-', 'kodepos' => '-', 'notelp1' => '-', 'notelp2' => '-', 'email' => '-', 'statusaktif' => '1', 'web' => '', 'namapemilik' => '-', 'jenisusaha' => '-', 'top' => '31', 'bank' => '-', 'rekeningbank' => '-', 'namarekening' => '-', 'jabatan' => '-', 'statusdaftarharga' => '97', 'kategoriusaha' => 'PENGECER', 'modifiedby' => 'ADMIN',]);
        supplier::create(['statusapproval' => 4, 'namasupplier' => 'JAYA BATTERY', 'namakontak' => '', 'alamat' => '', 'kota' => '', 'kodepos' => '', 'notelp1' => '', 'notelp2' => '', 'email' => '', 'statusaktif' => '1', 'web' => '', 'namapemilik' => '', 'jenisusaha' => '', 'top' => '0', 'bank' => '', 'rekeningbank' => '', 'namarekening' => '', 'jabatan' => '', 'statusdaftarharga' => '97', 'kategoriusaha' => '', 'modifiedby' => 'ADMIN',]);
        supplier::create(['statusapproval' => 4, 'namasupplier' => 'KANTOR PUSAT.', 'namakontak' => '-', 'alamat' => 'JL.R.A KARTINI', 'kota' => 'MEDAN', 'kodepos' => '-', 'notelp1' => '061-4522875', 'notelp2' => '-', 'email' => '-', 'statusaktif' => '1', 'web' => '-', 'namapemilik' => '-', 'jenisusaha' => '-', 'top' => '1', 'bank' => '-', 'rekeningbank' => '-', 'namarekening' => '-', 'jabatan' => '-', 'statusdaftarharga' => '96', 'kategoriusaha' => 'PRODUSEN', 'modifiedby' => 'ADMIN',]);
        supplier::create(['statusapproval' => 4, 'namasupplier' => 'MEGAH JAYA MOTOR', 'namakontak' => 'GUNAWAN', 'alamat' => 'JL. KAPTEN MUSLIM KOM. MEGACOM BLOK G-15', 'kota' => 'MEDAN', 'kodepos' => '-', 'notelp1' => '085102482572', 'notelp2' => '-', 'email' => '-', 'statusaktif' => '1', 'web' => '-', 'namapemilik' => 'GUNAWAN', 'jenisusaha' => 'AKI', 'top' => '30', 'bank' => '-', 'rekeningbank' => '-', 'namarekening' => '-', 'jabatan' => 'PEMILIK', 'statusdaftarharga' => '96', 'kategoriusaha' => 'DISTRIBUTOR', 'modifiedby' => 'ADMIN',]);
        supplier::create(['statusapproval' => 4, 'namasupplier' => 'MEGAH MOTOR', 'namakontak' => 'EDY AMRIN', 'alamat' => 'JALAN TILAK NO.17 C', 'kota' => 'MEDAN', 'kodepos' => '20214', 'notelp1' => '061-7332619', 'notelp2' => '-', 'email' => 'MEGAHMOTOR.ID@GMAIL.COM', 'statusaktif' => '1', 'web' => '', 'namapemilik' => 'EDY AMRIN', 'jenisusaha' => 'SPAREPARTS', 'top' => '18', 'bank' => 'BCA', 'rekeningbank' => '1950922598', 'namarekening' => 'JONA MAI/EDY AMRIN', 'jabatan' => 'PEMILIK', 'statusdaftarharga' => '96', 'kategoriusaha' => 'PENGECER', 'modifiedby' => 'ADMIN',]);
        supplier::create(['statusapproval' => 4, 'namasupplier' => 'MUSTIKA SAKTI', 'namakontak' => 'DEWIMA/AYEN', 'alamat' => 'JL.PROF H.M YAMIN NO.42', 'kota' => 'MEDAN', 'kodepos' => '-', 'notelp1' => '061-4560213', 'notelp2' => '061-4528905', 'email' => '-', 'statusaktif' => '1', 'web' => '', 'namapemilik' => 'DEWIMA', 'jenisusaha' => 'BAN MOBIL', 'top' => '30', 'bank' => 'MESTIKA', 'rekeningbank' => '10100006441', 'namarekening' => 'DARWIN', 'jabatan' => 'PENJUALAN', 'statusdaftarharga' => '96', 'kategoriusaha' => 'DISTRIBUTOR', 'modifiedby' => 'ADMIN',]);
        supplier::create(['statusapproval' => 4, 'namasupplier' => 'PADU JAYA', 'namakontak' => '-', 'alamat' => 'JL. MARELAN RAYA PASAI IV MEDAN MARELAN', 'kota' => 'MEDAN', 'kodepos' => '-', 'notelp1' => '-', 'notelp2' => '-', 'email' => '-', 'statusaktif' => '1', 'web' => '-', 'namapemilik' => '-', 'jenisusaha' => '-', 'top' => '1', 'bank' => '-', 'rekeningbank' => '-', 'namarekening' => '-', 'jabatan' => '-', 'statusdaftarharga' => '96', 'kategoriusaha' => 'PRODUSEN', 'modifiedby' => 'ADMIN',]);
        supplier::create(['statusapproval' => 4, 'namasupplier' => 'PERBENGKELAN YULI', 'namakontak' => '', 'alamat' => '', 'kota' => '', 'kodepos' => '', 'notelp1' => '', 'notelp2' => '', 'email' => '', 'statusaktif' => '1', 'web' => '', 'namapemilik' => '', 'jenisusaha' => '', 'top' => '0', 'bank' => '', 'rekeningbank' => '', 'namarekening' => '', 'jabatan' => '', 'statusdaftarharga' => '97', 'kategoriusaha' => '', 'modifiedby' => 'ADMIN',]);
        supplier::create(['statusapproval' => 4, 'namasupplier' => 'PT. POLA RAYA JAYA SAKTI', 'namakontak' => 'AQMAL ALMI', 'alamat' => 'JALAN SUTOMO NO.325 ', 'kota' => 'MEDAN', 'kodepos' => '-', 'notelp1' => '061 - 4581759', 'notelp2' => '-', 'email' => '-', 'statusaktif' => '1', 'web' => '-', 'namapemilik' => 'BASIR YAPAR', 'jenisusaha' => 'DISTRIBUTOR PELUMNAS PERTAMINA', 'top' => '1', 'bank' => '-', 'rekeningbank' => '-', 'namarekening' => '-', 'jabatan' => 'MARKETING', 'statusdaftarharga' => '96', 'kategoriusaha' => 'DISTRIBUTOR', 'modifiedby' => 'ADMIN',]);
        supplier::create(['statusapproval' => 4, 'namasupplier' => 'PT. PUTRA JISKO VULKANISIR', 'namakontak' => 'GUNAWAN WILLY/ACUN', 'alamat' => 'JALAN AMD (SIMPANG MANGGA BAWAH)', 'kota' => 'RANTAU PRAPAT', 'kodepos' => '-', 'notelp1' => '0624-22942', 'notelp2' => '-', 'email' => 'LIEWICUN@GMAIL.COM', 'statusaktif' => '1', 'web' => '', 'namapemilik' => 'KALFIN RUSLI', 'jenisusaha' => 'VULKANISIR BAN', 'top' => '30', 'bank' => 'MESTIKA - BII', 'rekeningbank' => '10.140.05.1593 - 20.9510.9505', 'namarekening' => 'PT.PUTRA JISKO VULKANISIR', 'jabatan' => 'MARKETING', 'statusdaftarharga' => '96', 'kategoriusaha' => 'PRODUSEN', 'modifiedby' => 'ADMIN',]);
        supplier::create(['statusapproval' => 4, 'namasupplier' => 'PT. SERDANG JAYA', 'namakontak' => 'HERRY', 'alamat' => 'JL. SUTOMO NO.252-254-256', 'kota' => 'MEDAN', 'kodepos' => '20231', 'notelp1' => '061 - 4565400', 'notelp2' => '061 - 4560222', 'email' => 'HERRYLESMANA@GMAIL.COM', 'statusaktif' => '1', 'web' => '-', 'namapemilik' => 'WITARMAN WILOPO', 'jenisusaha' => 'DISTRIBUTOR OLI', 'top' => '30', 'bank' => 'MANDIRI', 'rekeningbank' => '1050010661035', 'namarekening' => 'PT. SERDANG JAYA', 'jabatan' => 'MARKETING EXECUTIVE', 'statusdaftarharga' => '96', 'kategoriusaha' => 'DISTRIBUTOR', 'modifiedby' => 'ADMIN',]);
        supplier::create(['statusapproval' => 4, 'namasupplier' => 'PT. WIERATAMA RODA KARYA IND', 'namakontak' => 'DARWIE LIEONO', 'alamat' => 'JL. KL. YOS SUDARSO KM. 14,8 LINGK 1 MARTUBUNG, MEDAN LABUHAN, KOTA MEDAN', 'kota' => 'MEDAN', 'kodepos' => '-', 'notelp1' => '061-6850161', 'notelp2' => '061-6858692', 'email' => '-', 'statusaktif' => '1', 'web' => '', 'namapemilik' => 'LIE WAN TJAI', 'jenisusaha' => 'BENGKEL', 'top' => '30', 'bank' => 'MAY BANK/BII', 'rekeningbank' => '2467001179', 'namarekening' => 'DARWIE LIEONO', 'jabatan' => 'DIREKTUR', 'statusdaftarharga' => '97', 'kategoriusaha' => 'PENGECER', 'modifiedby' => 'ADMIN',]);
        supplier::create(['statusapproval' => 4, 'namasupplier' => 'PT.PATEN JAYA VULKANISIR', 'namakontak' => 'LEO HANDY / GUNAWAN WILLY', 'alamat' => 'JL. H.ADAM MALIK (LINTAS SUMATERA)', 'kota' => 'RANTAU PRAPAT', 'kodepos' => '-', 'notelp1' => '08116220699', 'notelp2' => '-', 'email' => 'LEO_PATIKAWA@YAHOO.COM', 'statusaktif' => '1', 'web' => '-', 'namapemilik' => 'KALFIN RUSLI', 'jenisusaha' => 'VULKANISIR BAN', 'top' => '30', 'bank' => 'PANIN', 'rekeningbank' => '5382036093', 'namarekening' => 'KALFIN RUSLI', 'jabatan' => 'DIREKTUR / SUPERVISOR', 'statusdaftarharga' => '96', 'kategoriusaha' => 'PRODUSEN', 'modifiedby' => 'ADMIN',]);
        supplier::create(['statusapproval' => 4, 'namasupplier' => 'PT.POLA RAYA JAYA SAKTI', 'namakontak' => '-', 'alamat' => 'JN. SUTOMO NO 325', 'kota' => 'MEDAN', 'kodepos' => '-', 'notelp1' => '4563416-4149279', 'notelp2' => '-', 'email' => '-', 'statusaktif' => '2', 'web' => '-', 'namapemilik' => '-', 'jenisusaha' => '-', 'top' => '1', 'bank' => '-', 'rekeningbank' => '-', 'namarekening' => '-', 'jabatan' => '-', 'statusdaftarharga' => '96', 'kategoriusaha' => 'DISTRIBUTOR', 'modifiedby' => 'ADMIN',]);
        supplier::create(['statusapproval' => 4, 'namasupplier' => 'PT.PRIMA JAYA (IBU ACCU)', 'namakontak' => '-', 'alamat' => 'JLN ASIA NO 14/36', 'kota' => '-', 'kodepos' => '-', 'notelp1' => '75122107-7366985', 'notelp2' => '-', 'email' => '-', 'statusaktif' => '1', 'web' => '', 'namapemilik' => '-', 'jenisusaha' => '-', 'top' => '30', 'bank' => '-', 'rekeningbank' => '-', 'namarekening' => '-', 'jabatan' => '-', 'statusdaftarharga' => '97', 'kategoriusaha' => 'PENGECER', 'modifiedby' => 'ADMIN',]);
        supplier::create(['statusapproval' => 4, 'namasupplier' => 'SAUDARA MOTOR', 'namakontak' => 'PAU AGUSTIA LIUS', 'alamat' => 'JL.TILAK NO.10 KEL.SEI RENGAS I KEC.MEDAN KOTA', 'kota' => 'MEDAN', 'kodepos' => '20214', 'notelp1' => '061-7346939', 'notelp2' => '061-7351075', 'email' => 'PL_LIUS@YAHOO.COM', 'statusaktif' => '1', 'web' => '', 'namapemilik' => 'PAU AGUSTIA LIUS', 'jenisusaha' => 'SPAREPARTS', 'top' => '18', 'bank' => 'BCA', 'rekeningbank' => '1950923268', 'namarekening' => 'MARIA AMRIN/PAUL AGUSTIA LIUS', 'jabatan' => 'PEMILIK', 'statusdaftarharga' => '96', 'kategoriusaha' => 'PENGECER', 'modifiedby' => 'ADMIN',]);
        supplier::create(['statusapproval' => 4, 'namasupplier' => 'SENG ENG SENG', 'namakontak' => 'LITA', 'alamat' => 'JL PENGABDIAN DUSUN I DELI SERDANG NO.102 MEDAN', 'kota' => 'TEMBUNG', 'kodepos' => '-', 'notelp1' => '061-7380360', 'notelp2' => '061-7380784', 'email' => '-', 'statusaktif' => '2', 'web' => '', 'namapemilik' => 'LAI MUNG HWE (BP.AHUI)', 'jenisusaha' => 'VULKANISIR', 'top' => '30', 'bank' => 'OCBC NISP', 'rekeningbank' => '180800015331', 'namarekening' => 'PT.BAS', 'jabatan' => 'PERSONALIA', 'statusdaftarharga' => '96', 'kategoriusaha' => 'PRODUSEN', 'modifiedby' => 'ADMIN',]);
        supplier::create(['statusapproval' => 4, 'namasupplier' => 'SINAR RADIATOR', 'namakontak' => '-', 'alamat' => '-', 'kota' => 'MEDAN', 'kodepos' => '-', 'notelp1' => '-', 'notelp2' => '085370710338', 'email' => '-', 'statusaktif' => '1', 'web' => '-', 'namapemilik' => '-', 'jenisusaha' => '-', 'top' => '1', 'bank' => '-', 'rekeningbank' => '-', 'namarekening' => '-', 'jabatan' => '-', 'statusdaftarharga' => '96', 'kategoriusaha' => 'SUB-AGEN', 'modifiedby' => 'ADMIN',]);
        supplier::create(['statusapproval' => 4, 'namasupplier' => 'SING ENG SENG', 'namakontak' => 'LITA', 'alamat' => 'JL.PENGABDIAN DUSUN I-DELI SERDANG NO.12 MEDAN', 'kota' => 'TEMBUNG', 'kodepos' => '-', 'notelp1' => '061-7380360', 'notelp2' => '061-7380784', 'email' => '-', 'statusaktif' => '1', 'web' => '', 'namapemilik' => 'LAI MUNG HWE (BP.AHUI)', 'jenisusaha' => 'VULKANISIR', 'top' => '30', 'bank' => 'OCBC NISP', 'rekeningbank' => '180800015331', 'namarekening' => 'PT.BAS', 'jabatan' => 'PERSONALIA', 'statusdaftarharga' => '96', 'kategoriusaha' => 'PRODUSEN', 'modifiedby' => 'ADMIN',]);
        supplier::create(['statusapproval' => 4, 'namasupplier' => 'SUMATERA TRANS JAYA', 'namakontak' => 'RICO CHANDRA', 'alamat' => 'JL. CEMARA NO.86 KOMPLEK GREAT ARCADE ', 'kota' => 'MEDAN', 'kodepos' => '21105', 'notelp1' => '08527595957', 'notelp2' => '-', 'email' => 'RICOCHANDRASITOMPUL@GMAIL.COM', 'statusaktif' => '1', 'web' => '-', 'namapemilik' => 'CHARLES', 'jenisusaha' => 'DISTRIBUTOR SPAREPART', 'top' => '60', 'bank' => 'MANDIRI', 'rekeningbank' => '1060009863559', 'namarekening' => 'CHARLES', 'jabatan' => 'MANAGER', 'statusdaftarharga' => '96', 'kategoriusaha' => 'DISTRIBUTOR', 'modifiedby' => 'ADMIN',]);
        supplier::create(['statusapproval' => 4, 'namasupplier' => 'TAS SBY', 'namakontak' => '-', 'alamat' => '-', 'kota' => '-', 'kodepos' => '-', 'notelp1' => '-', 'notelp2' => '-', 'email' => '-', 'statusaktif' => '1', 'web' => '', 'namapemilik' => '-', 'jenisusaha' => '-', 'top' => '30', 'bank' => '-', 'rekeningbank' => '', 'namarekening' => '', 'jabatan' => 'PEMILIK', 'statusdaftarharga' => '97', 'kategoriusaha' => 'PENGECER', 'modifiedby' => 'ADMIN',]);
        supplier::create(['statusapproval' => 4, 'namasupplier' => 'TITIPAN CABANG', 'namakontak' => '-', 'alamat' => '-', 'kota' => '-', 'kodepos' => '-', 'notelp1' => '-', 'notelp2' => '-', 'email' => '-', 'statusaktif' => '1', 'web' => '-', 'namapemilik' => '-', 'jenisusaha' => '-', 'top' => '30', 'bank' => '-', 'rekeningbank' => '-', 'namarekening' => '-', 'jabatan' => '-', 'statusdaftarharga' => '96', 'kategoriusaha' => 'DISTRIBUTOR', 'modifiedby' => 'ADMIN',]);
        supplier::create(['statusapproval' => 4, 'namasupplier' => 'UNI DIESEL', 'namakontak' => '-', 'alamat' => '-', 'kota' => '-', 'kodepos' => '-', 'notelp1' => '-', 'notelp2' => '-', 'email' => '-', 'statusaktif' => '1', 'web' => '', 'namapemilik' => '-', 'jenisusaha' => '-', 'top' => '7', 'bank' => '-', 'rekeningbank' => '-', 'namarekening' => '-', 'jabatan' => '-', 'statusdaftarharga' => '97', 'kategoriusaha' => 'PENGECER', 'modifiedby' => 'ADMIN',]);
    }
}
