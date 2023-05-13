<?php

namespace Database\Seeders;

use App\Models\HutangDetail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HutangDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete HutangDetail");
        DB::statement("DBCC CHECKIDENT ('HutangDetail', RESEED, 1);");

        hutangdetail::create(['hutang_id' => '1', 'nobukti' => 'EHT 0001/II/2023', 'tgljatuhtempo' => '1970/1/1', 'total' => '36000', 'cicilan' => '0', 'keterangan' => 'ORDER SPAREPART DI HARAPAN JAYA  UNTUK TRADO BK 8145 CE YG AKAN DIKIRIM KE CBG MAKASAR (INPUT TGL 1 FEBRUARI KARENA BRG DI ORDER TGL 30/1/2023 SAAT MEKANIK PLG KERJA, BRG DAN BON DIANTAR TGL 31/1/2023 SORE DAN MENUNGGU PENAMAAN PUSAT)', 'totalbayar' => '0', 'modifiedby' => 'ADMIN',]);
        hutangdetail::create(['hutang_id' => '1', 'nobukti' => 'EHT 0001/II/2023', 'tgljatuhtempo' => '1970/1/1', 'total' => '36000', 'cicilan' => '0', 'keterangan' => 'ORDER SPAREPART DI HARAPAN JAYA  UNTUK TRADO BK 8145 CE YG AKAN DIKIRIM KE CBG MAKASAR (INPUT TGL 1 FEBRUARI KARENA BRG DI ORDER TGL 30/1/2023 SAAT MEKANIK PLG KERJA, BRG DAN BON DIANTAR TGL 31/1/2023 SORE DAN MENUNGGU PENAMAAN PUSAT)', 'totalbayar' => '0', 'modifiedby' => 'ADMIN',]);
        hutangdetail::create(['hutang_id' => '1', 'nobukti' => 'EHT 0001/II/2023', 'tgljatuhtempo' => '1970/1/1', 'total' => '14000', 'cicilan' => '0', 'keterangan' => 'ORDER SPAREPART DI HARAPAN JAYA  UNTUK TRADO BK 8145 CE YG AKAN DIKIRIM KE CBG MAKASAR (INPUT TGL 1 FEBRUARI KARENA BRG DI ORDER TGL 30/1/2023 SAAT MEKANIK PLG KERJA, BRG DAN BON DIANTAR TGL 31/1/2023 SORE DAN MENUNGGU PENAMAAN PUSAT)', 'totalbayar' => '0', 'modifiedby' => 'ADMIN',]);
        hutangdetail::create(['hutang_id' => '1', 'nobukti' => 'EHT 0001/II/2023', 'tgljatuhtempo' => '1970/1/1', 'total' => '15000', 'cicilan' => '0', 'keterangan' => 'ORDER SPAREPART DI HARAPAN JAYA  UNTUK TRADO BK 8145 CE YG AKAN DIKIRIM KE CBG MAKASAR (INPUT TGL 1 FEBRUARI KARENA BRG DI ORDER TGL 30/1/2023 SAAT MEKANIK PLG KERJA, BRG DAN BON DIANTAR TGL 31/1/2023 SORE DAN MENUNGGU PENAMAAN PUSAT)', 'totalbayar' => '0', 'modifiedby' => 'ADMIN',]);
        hutangdetail::create(['hutang_id' => '1', 'nobukti' => 'EHT 0001/II/2023', 'tgljatuhtempo' => '1970/1/1', 'total' => '12000', 'cicilan' => '0', 'keterangan' => 'ORDER SPAREPART DI HARAPAN JAYA  UNTUK TRADO BK 8145 CE YG AKAN DIKIRIM KE CBG MAKASAR (INPUT TGL 1 FEBRUARI KARENA BRG DI ORDER TGL 30/1/2023 SAAT MEKANIK PLG KERJA, BRG DAN BON DIANTAR TGL 31/1/2023 SORE DAN MENUNGGU PENAMAAN PUSAT)', 'totalbayar' => '0', 'modifiedby' => 'ADMIN',]);
        hutangdetail::create(['hutang_id' => '1', 'nobukti' => 'EHT 0001/II/2023', 'tgljatuhtempo' => '1970/1/1', 'total' => '20000', 'cicilan' => '0', 'keterangan' => 'ORDER SPAREPART DI HARAPAN JAYA  UNTUK TRADO BK 8145 CE YG AKAN DIKIRIM KE CBG MAKASAR (INPUT TGL 1 FEBRUARI KARENA BRG DI ORDER TGL 30/1/2023 SAAT MEKANIK PLG KERJA, BRG DAN BON DIANTAR TGL 31/1/2023 SORE DAN MENUNGGU PENAMAAN PUSAT)', 'totalbayar' => '0', 'modifiedby' => 'ADMIN',]);
        hutangdetail::create(['hutang_id' => '1', 'nobukti' => 'EHT 0001/II/2023', 'tgljatuhtempo' => '1970/1/1', 'total' => '8000', 'cicilan' => '0', 'keterangan' => 'ORDER SPAREPART DI HARAPAN JAYA  UNTUK TRADO BK 8145 CE YG AKAN DIKIRIM KE CBG MAKASAR (INPUT TGL 1 FEBRUARI KARENA BRG DI ORDER TGL 30/1/2023 SAAT MEKANIK PLG KERJA, BRG DAN BON DIANTAR TGL 31/1/2023 SORE DAN MENUNGGU PENAMAAN PUSAT)', 'totalbayar' => '0', 'modifiedby' => 'ADMIN',]);
        hutangdetail::create(['hutang_id' => '1', 'nobukti' => 'EHT 0001/II/2023', 'tgljatuhtempo' => '1970/1/1', 'total' => '20000', 'cicilan' => '0', 'keterangan' => 'ORDER SPAREPART DI HARAPAN JAYA  UNTUK TRADO BK 8145 CE YG AKAN DIKIRIM KE CBG MAKASAR (INPUT TGL 1 FEBRUARI KARENA BRG DI ORDER TGL 30/1/2023 SAAT MEKANIK PLG KERJA, BRG DAN BON DIANTAR TGL 31/1/2023 SORE DAN MENUNGGU PENAMAAN PUSAT)', 'totalbayar' => '0', 'modifiedby' => 'ADMIN',]);
        hutangdetail::create(['hutang_id' => '1', 'nobukti' => 'EHT 0001/II/2023', 'tgljatuhtempo' => '1970/1/1', 'total' => '20000', 'cicilan' => '0', 'keterangan' => 'ORDER SPAREPART DI HARAPAN JAYA  UNTUK TRADO BK 8145 CE YG AKAN DIKIRIM KE CBG MAKASAR (INPUT TGL 1 FEBRUARI KARENA BRG DI ORDER TGL 30/1/2023 SAAT MEKANIK PLG KERJA, BRG DAN BON DIANTAR TGL 31/1/2023 SORE DAN MENUNGGU PENAMAAN PUSAT)', 'totalbayar' => '0', 'modifiedby' => 'ADMIN',]);
        hutangdetail::create(['hutang_id' => '2', 'nobukti' => 'EHT 0002/II/2023', 'tgljatuhtempo' => '1970/1/1', 'total' => '200000', 'cicilan' => '0', 'keterangan' => 'ORDER SPAREPART DI HOKI JAYA', 'totalbayar' => '0', 'modifiedby' => 'ADMIN',]);
        hutangdetail::create(['hutang_id' => '2', 'nobukti' => 'EHT 0002/II/2023', 'tgljatuhtempo' => '1970/1/1', 'total' => '21000', 'cicilan' => '0', 'keterangan' => 'ORDER SPAREPART DI HOKI JAYA', 'totalbayar' => '0', 'modifiedby' => 'ADMIN',]);
        hutangdetail::create(['hutang_id' => '2', 'nobukti' => 'EHT 0002/II/2023', 'tgljatuhtempo' => '1970/1/1', 'total' => '110000', 'cicilan' => '0', 'keterangan' => 'ORDER SPAREPART DI HOKI JAYA', 'totalbayar' => '0', 'modifiedby' => 'ADMIN',]);
        hutangdetail::create(['hutang_id' => '2', 'nobukti' => 'EHT 0002/II/2023', 'tgljatuhtempo' => '1970/1/1', 'total' => '20000', 'cicilan' => '0', 'keterangan' => 'ORDER SPAREPART DI HOKI JAYA', 'totalbayar' => '0', 'modifiedby' => 'ADMIN',]);
    }
}
