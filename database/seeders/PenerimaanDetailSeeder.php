<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PenerimaanDetail;
use Illuminate\Support\Facades\DB;

class PenerimaanDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete penerimaandetail");
        DB::statement("DBCC CHECKIDENT ('penerimaandetail', RESEED, 1);");

        penerimaandetail::create(['penerimaan_id' => '1', 'nobukti' => 'BMT-M BCA3 0001/II/2023', 'nowarkat' => '', 'tgljatuhtempo' => '2023/2/1', 'nominal' => '90000000', 'coadebet' => '01.02.02.05', 'coakredit' => '05.03.01.01', 'keterangan' => 'DITERIMA DARI KANTOR PUSAT DANA MINGGUAN TRUCKING', 'bank_id' => '4', 'invoice_nobukti' => '-', 'bankpelanggan_id' => '0', 'pelunasanpiutang_nobukti' => '-', 'bulanbeban' => '1900/1/1', 'modifiedby' => 'ADMIN',]);
        penerimaandetail::create(['penerimaan_id' => '1', 'nobukti' => 'BMT-M BCA3 0001/II/2023', 'nowarkat' => '', 'tgljatuhtempo' => '2023/2/1', 'nominal' => '100000', 'coadebet' => '01.02.02.05', 'coakredit' => '03.02.02.12', 'keterangan' => 'DITERIMA DARI KANTOR PUSAT ATAS PENGEMBALIAN BIAYA TOP UP SALDO TOL CARD MOBIL BP. ACHIANG TGL. 17 JANUARI 2023', 'bank_id' => '4', 'invoice_nobukti' => '-', 'bankpelanggan_id' => '0', 'pelunasanpiutang_nobukti' => '-', 'bulanbeban' => '1900/1/1', 'modifiedby' => 'ADMIN',]);
        penerimaandetail::create(['penerimaan_id' => '1', 'nobukti' => 'BMT-M BCA3 0001/II/2023', 'nowarkat' => '', 'tgljatuhtempo' => '2023/2/1', 'nominal' => '100000', 'coadebet' => '01.02.02.05', 'coakredit' => '03.02.02.12', 'keterangan' => 'DITERIMA DARI KANTOR PUSAT ATAS PENGEMBALIAN BIAYA TOP UP SALDO TOL CARD MOBIL BP. ACHIANG TGL. 19 JANUARI 2023', 'bank_id' => '4', 'invoice_nobukti' => '-', 'bankpelanggan_id' => '0', 'pelunasanpiutang_nobukti' => '-', 'bulanbeban' => '1900/1/1', 'modifiedby' => 'ADMIN',]);
        penerimaandetail::create(['penerimaan_id' => '1', 'nobukti' => 'BMT-M BCA3 0001/II/2023', 'nowarkat' => '', 'tgljatuhtempo' => '2023/2/1', 'nominal' => '100000', 'coadebet' => '01.02.02.05', 'coakredit' => '03.02.02.12', 'keterangan' => 'DITERIMA DARI KANTOR PUSAT ATAS PENGEMBALIAN BIAYA TOP UP SALDO TOL CARD MOBIL BP. ACHIANG TGL. 25 JANUARI 2023', 'bank_id' => '4', 'invoice_nobukti' => '-', 'bankpelanggan_id' => '0', 'pelunasanpiutang_nobukti' => '-', 'bulanbeban' => '1900/1/1', 'modifiedby' => 'ADMIN',]);
        penerimaandetail::create(['penerimaan_id' => '1', 'nobukti' => 'BMT-M BCA3 0001/II/2023', 'nowarkat' => '', 'tgljatuhtempo' => '2023/2/1', 'nominal' => '1290500', 'coadebet' => '01.02.02.05', 'coakredit' => '03.02.02.12', 'keterangan' => 'DITERIMA DARI KANTOR PUSAT ATAS PENGEMBALIAN BIAYA BBM MOBIL BP. ACHIANG TGL. 27 JANUARI 2022', 'bank_id' => '4', 'invoice_nobukti' => '-', 'bankpelanggan_id' => '0', 'pelunasanpiutang_nobukti' => '-', 'bulanbeban' => '1900/1/1', 'modifiedby' => 'ADMIN',]);
        penerimaandetail::create(['penerimaan_id' => '2', 'nobukti' => 'BMT-M BCA3 0002/II/2023', 'nowarkat' => '-', 'tgljatuhtempo' => '2023/2/1', 'nominal' => '700000', 'coadebet' => '01.02.02.05', 'coakredit' => '01.08.01.06', 'keterangan' => 'PELUNASAM B. SEWA TAS AHAI BULAN JANUARI 2023 INV. 0005/I/2023 = RP. 700.000', 'bank_id' => '4', 'invoice_nobukti' => 'INV 0005/I/2023', 'bankpelanggan_id' => '0', 'pelunasanpiutang_nobukti' => 'PPT 0001/II/2023', 'bulanbeban' => '1900/1/1', 'modifiedby' => 'ADMIN',]);
    }
}
