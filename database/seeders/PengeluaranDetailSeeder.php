<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PengeluaranDetail;
use Illuminate\Support\Facades\DB;

class PengeluaranDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {



        DB::statement("delete pengeluarandetail");
        DB::statement("DBCC CHECKIDENT ('pengeluarandetail', RESEED, 1);");


        pengeluarandetail::create(['pengeluaran_id' => '1', 'nobukti' => 'KBT 0001/II/2023', 'nowarkat' => '', 'tgljatuhtempo' => '1970/1/1', 'nominal' => '19645', 'coadebet' => '01.05.02.02', 'coakredit' => '01.01.01.02', 'keterangan' => 'GAJI MINUS SUPIR CHANDRA BK 8743 BU TGL. 01 FEBRUARI 2023', 'bulanbeban' => '1900/1/1', 'modifiedby' => 'ADMIN',]);
        pengeluarandetail::create(['pengeluaran_id' => '2', 'nobukti' => 'KBT 0002/II/2023', 'nowarkat' => '', 'tgljatuhtempo' => '1970/1/1', 'nominal' => '100780', 'coadebet' => '01.05.02.02', 'coakredit' => '01.01.01.02', 'keterangan' => 'GAJI MINUS SUPIR ERIKSON BK 8264 FB TGL. 01 FEBRUARI 2023', 'bulanbeban' => '1900/1/1', 'modifiedby' => 'ADMIN',]);
        pengeluarandetail::create(['pengeluaran_id' => '3', 'nobukti' => 'KBT 0003/II/2023', 'nowarkat' => '', 'tgljatuhtempo' => '1970/1/1', 'nominal' => '33408', 'coadebet' => '01.05.02.02', 'coakredit' => '01.01.01.02', 'keterangan' => 'GAJI MINUS SUPIR SAHBUDIN  BK 8178 EW TGL. 01 FEBRUARI 2023', 'bulanbeban' => '1900/1/1', 'modifiedby' => 'ADMIN',]);
        pengeluarandetail::create(['pengeluaran_id' => '4', 'nobukti' => 'KBT 0004/II/2023', 'nowarkat' => '', 'tgljatuhtempo' => '1970/1/1', 'nominal' => '215736', 'coadebet' => '01.05.02.02', 'coakredit' => '01.01.01.02', 'keterangan' => 'GAJI MINUS SUPIR SULAIMAN B 9668 QZ TGL. 01 FEBRUARI 2023', 'bulanbeban' => '1900/1/1', 'modifiedby' => 'ADMIN',]);
        pengeluarandetail::create(['pengeluaran_id' => '5', 'nobukti' => 'KBT 0006/II/2023', 'nowarkat' => '', 'tgljatuhtempo' => '2023/2/1', 'nominal' => '75000', 'coadebet' => '03.02.02.99', 'coakredit' => '01.01.01.02', 'keterangan' => 'PENGEMBALIAN TERIMA DARI SUPIR CHANDRA BK 8743 BU UNTUK PREDIKSI GAJI MINUS TGL. 01 FEBRUARI 2023', 'bulanbeban' => '1900/1/1', 'modifiedby' => 'ADMIN',]);
        pengeluarandetail::create(['pengeluaran_id' => '6', 'nobukti' => 'KBT 0007/II/2023', 'nowarkat' => '', 'tgljatuhtempo' => '2023/2/1', 'nominal' => '755000', 'coadebet' => '07.02.01.12', 'coakredit' => '01.01.01.02', 'keterangan' => 'Setor Fee Harry Ananda atas ritasi unit trado yang jalan Bulan Januari 2023 = 302 Unit @ Rp. 2.500', 'bulanbeban' => '1900/1/1', 'modifiedby' => 'AYEN',]);
        pengeluarandetail::create(['pengeluaran_id' => '6', 'nobukti' => 'KBT 0007/II/2023', 'nowarkat' => '', 'tgljatuhtempo' => '2023/2/1', 'nominal' => '2000', 'coadebet' => '07.02.01.34', 'coakredit' => '01.01.01.02', 'keterangan' => 'Biaya Adm Bank untuk Setor Fee Harry Ananda atas ritasi unit trado yang jalan Bulan Januari 2023 = 302 Unit @ Rp. 2.500', 'bulanbeban' => '1900/1/1', 'modifiedby' => 'AYEN',]);
        pengeluarandetail::create(['pengeluaran_id' => '6', 'nobukti' => 'KBT 0007/II/2023', 'nowarkat' => '', 'tgljatuhtempo' => '2023/2/1', 'nominal' => '7100000', 'coadebet' => '01.09.01.04', 'coakredit' => '01.01.01.02', 'keterangan' => 'Setor ke Cabang Jakarta atas Biaya Gaji Mekanik Jakarta yang dinas di Medan a/n. Bp. Abdul Manan Sutisna Bulan Januari 2023', 'bulanbeban' => '1900/1/1', 'modifiedby' => 'AYEN',]);
        pengeluarandetail::create(['pengeluaran_id' => '6', 'nobukti' => 'KBT 0007/II/2023', 'nowarkat' => '', 'tgljatuhtempo' => '2023/2/1', 'nominal' => '2000', 'coadebet' => '07.02.01.34', 'coakredit' => '01.01.01.02', 'keterangan' => 'Biaya Adm bank untuk Setor ke Cabang Jakarta atas Biaya Gaji Mekanik Jakarta yang dinas di Medan a/n. Bp. Abdul Manan Sutisna Bulan Januari 2023', 'bulanbeban' => '1900/1/1', 'modifiedby' => 'AYEN',]);
        pengeluarandetail::create(['pengeluaran_id' => '7', 'nobukti' => 'KBT 0008/II/2023', 'nowarkat' => '', 'tgljatuhtempo' => '2023/2/1', 'nominal' => '2000000', 'coadebet' => '07.02.01.13', 'coakredit' => '01.01.01.02', 'keterangan' => 'Biaya Jasa Pengamanan Trado di Belawan MAR 84 Bulan Februari 2023', 'bulanbeban' => '1900/1/1', 'modifiedby' => 'AYEN',]);
    }
}
