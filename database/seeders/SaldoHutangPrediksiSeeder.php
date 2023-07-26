<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SaldoHutangPrediksi;
use Illuminate\Support\Facades\DB;

class SaldoHutangPrediksiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete saldohutangprediksi");
        DB::statement("DBCC CHECKIDENT ('saldohutangprediksi', RESEED, 1);");

        saldohutangprediksi::create([ 'nobukti' => 'Adj FEB-23', 'keterangan' => 'Hutang Prediksi', 'nominal' => '1200188.00',]);
        saldohutangprediksi::create([ 'nobukti' => 'Adj MAR-23', 'keterangan' => 'Hutang Prediksi', 'nominal' => '8592521.00',]);
        saldohutangprediksi::create([ 'nobukti' => 'Adj APRL-23', 'keterangan' => 'Hutang Prediksi', 'nominal' => '543966.00',]);
        saldohutangprediksi::create([ 'nobukti' => 'EHT 0003/III/2023', 'keterangan' => 'Biaya Beli Sparepart 10 Pcs Piston Air Dryer Titipan Cabang Makassar di Hoki Jaya Inv. 0001756', 'nominal' => '1250000.00',]);
        saldohutangprediksi::create([ 'nobukti' => 'EHT 0001/IV/2023', 'keterangan' => 'Biaya Beli Sparepart Titipan Cabang Makassar di Hoki Jaya Inv. 0003836', 'nominal' => '940000.00',]);
        saldohutangprediksi::create([ 'nobukti' => 'EHT 0002/IV/2023', 'keterangan' => 'Beli ATK dari Infomedia Komputer untuk keperluan kantor Inv. E23040228', 'nominal' => '357000.00',]);
        saldohutangprediksi::create([ 'nobukti' => 'Adj APRL-2301', 'keterangan' => 'BIAYA PEMELIHARAAN KAWASAN KIM BULAN APRL 2023', 'nominal' => '1898845.00',]);
        saldohutangprediksi::create([ 'nobukti' => 'Adj APRL-2302', 'keterangan' => 'BIAYA PENGOLAHAN LIMBAH DOMESTIK BULAN APRL 2023', 'nominal' => '83250.00',]);
        saldohutangprediksi::create([ 'nobukti' => 'EBS 0004/V/2023', 'keterangan' => 'Borongan', 'nominal' => '125000.00',]);
        saldohutangprediksi::create([ 'nobukti' => 'EBS 0001/V/2023', 'keterangan' => 'Borongan', 'nominal' => '3103654.00',]);
        saldohutangprediksi::create([ 'nobukti' => 'EBS 0001/V/2023', 'keterangan' => 'Extra', 'nominal' => '863000.00',]);
        saldohutangprediksi::create([ 'nobukti' => 'EBS 0001/V/2023', 'keterangan' => 'uang makan', 'nominal' => '1550000.00',]);
        saldohutangprediksi::create([ 'nobukti' => 'EBS 0002/V/2023', 'keterangan' => 'Borongan', 'nominal' => '7240885.00',]);
        saldohutangprediksi::create([ 'nobukti' => 'EBS 0002/V/2023', 'keterangan' => 'Extra', 'nominal' => '1390700.00',]);
        saldohutangprediksi::create([ 'nobukti' => 'EBS 0002/V/2023', 'keterangan' => 'uang makan', 'nominal' => '2250000.00',]);
        saldohutangprediksi::create([ 'nobukti' => 'Komisi M1 Bulan Apr', 'keterangan' => 'Komisi M1 Bulan Apr', 'nominal' => '2430000.00',]);
        saldohutangprediksi::create([ 'nobukti' => 'Komisi M2 Bulan Apr', 'keterangan' => 'Komisi M2 Bulan Apr', 'nominal' => '1660000.00',]);
        saldohutangprediksi::create([ 'nobukti' => 'Komisi M3 Bulan Apr', 'keterangan' => 'Komisi M3 Bulan Apr', 'nominal' => '820000.00',]);
        saldohutangprediksi::create([ 'nobukti' => 'Komisi M4 Bulan Apr', 'keterangan' => 'Komisi M4 Bulan Apr', 'nominal' => '640000.00',]);
        saldohutangprediksi::create([ 'nobukti' => 'EBS 0003/I/2023', 'keterangan' => 'EBS 0003/I/2023', 'nominal' => '40000.00',]);
        saldohutangprediksi::create([ 'nobukti' => 'EBS 0004/I/2023', 'keterangan' => 'EBS 0004/I/2023', 'nominal' => '40000.00',]);
        saldohutangprediksi::create([ 'nobukti' => 'EBS 0005/I/2023', 'keterangan' => 'EBS 0005/I/2023', 'nominal' => '20000.00',]);
        saldohutangprediksi::create([ 'nobukti' => 'EBS 0006/I/2023', 'keterangan' => 'EBS 0006/I/2023', 'nominal' => '10000.00',]);
        saldohutangprediksi::create([ 'nobukti' => 'EBS 0007/I/2023', 'keterangan' => 'EBS 0007/I/2023', 'nominal' => '30000.00',]);
        saldohutangprediksi::create([ 'nobukti' => 'EBS 0009/I/2023', 'keterangan' => 'EBS 0009/I/2023', 'nominal' => '15000.00',]);
        saldohutangprediksi::create([ 'nobukti' => 'EBS 0010/I/2023', 'keterangan' => 'EBS 0010/I/2023', 'nominal' => '25000.00',]);
        saldohutangprediksi::create([ 'nobukti' => 'EBS 0011/I/2023', 'keterangan' => 'EBS 0011/I/2023', 'nominal' => '35000.00',]);
        saldohutangprediksi::create([ 'nobukti' => 'EBS 0012/I/2023', 'keterangan' => 'EBS 0012/I/2023', 'nominal' => '25000.00',]);
        saldohutangprediksi::create([ 'nobukti' => 'EBS 0013/I/2023', 'keterangan' => 'EBS 0013/I/2023', 'nominal' => '80000.00',]);
        saldohutangprediksi::create([ 'nobukti' => 'EBS 0001/II/2023', 'keterangan' => 'EBS 0001/II/2023', 'nominal' => '50000.00',]);
        saldohutangprediksi::create([ 'nobukti' => 'EBS 0002/II/2023', 'keterangan' => 'EBS 0002/II/2023', 'nominal' => '50000.00',]);
        saldohutangprediksi::create([ 'nobukti' => 'EBS 0003/II/2023', 'keterangan' => 'EBS 0003/II/2023', 'nominal' => '95000.00',]);
        saldohutangprediksi::create([ 'nobukti' => 'EBS 0004/II/2023', 'keterangan' => 'EBS 0004/II/2023', 'nominal' => '80000.00',]);
        saldohutangprediksi::create([ 'nobukti' => 'EBS 0005/II/2023', 'keterangan' => 'EBS 0005/II/2023', 'nominal' => '60000.00',]);
        saldohutangprediksi::create([ 'nobukti' => 'EBS 0006/II/2023', 'keterangan' => 'EBS 0006/II/2023', 'nominal' => '110000.00',]);
        saldohutangprediksi::create([ 'nobukti' => 'EBS 0007/II/2023', 'keterangan' => 'EBS 0007/II/2023', 'nominal' => '55000.00',]);
        saldohutangprediksi::create([ 'nobukti' => 'EBS 0008/II/2023', 'keterangan' => 'EBS 0008/II/2023', 'nominal' => '100000.00',]);
        saldohutangprediksi::create([ 'nobukti' => 'EBS 0009/II/2023', 'keterangan' => 'EBS 0009/II/2023', 'nominal' => '90000.00',]);
        saldohutangprediksi::create([ 'nobukti' => 'EBS 0010/II/2023', 'keterangan' => 'EBS 0010/II/2023', 'nominal' => '55000.00',]);
        saldohutangprediksi::create([ 'nobukti' => 'EBS 0011/II/2023', 'keterangan' => 'EBS 0011/II/2023', 'nominal' => '95000.00',]);
        saldohutangprediksi::create([ 'nobukti' => 'EBS 0012/II/2023', 'keterangan' => 'EBS 0012/II/2023', 'nominal' => '45000.00',]);
        saldohutangprediksi::create([ 'nobukti' => 'EBS 0001/III/2023', 'keterangan' => 'EBS 0001/III/2023', 'nominal' => '90000.00',]);
        saldohutangprediksi::create([ 'nobukti' => 'EBS 0002/III/2023', 'keterangan' => 'EBS 0002/III/2023', 'nominal' => '145000.00',]);
        saldohutangprediksi::create([ 'nobukti' => 'EBS 0003/III/2023', 'keterangan' => 'EBS 0003/III/2023', 'nominal' => '135000.00',]);
        saldohutangprediksi::create([ 'nobukti' => 'EBS 0004/III/2023', 'keterangan' => 'EBS 0004/III/2023', 'nominal' => '155000.00',]);
        saldohutangprediksi::create([ 'nobukti' => 'EBS 0005/III/2023', 'keterangan' => 'EBS 0005/III/2023', 'nominal' => '120000.00',]);
        saldohutangprediksi::create([ 'nobukti' => 'EBS 0006/III/2023', 'keterangan' => 'EBS 0006/III/2023', 'nominal' => '235000.00',]);
        saldohutangprediksi::create([ 'nobukti' => 'EBS 0007/III/2023', 'keterangan' => 'EBS 0007/III/2023', 'nominal' => '110000.00',]);
        saldohutangprediksi::create([ 'nobukti' => 'EBS 0008/III/2023', 'keterangan' => 'EBS 0008/III/2023', 'nominal' => '135000.00',]);
        saldohutangprediksi::create([ 'nobukti' => 'EBS 0009/III/2023', 'keterangan' => 'EBS 0009/III/2023', 'nominal' => '230000.00',]);
        saldohutangprediksi::create([ 'nobukti' => 'EBS 0010/III/2023', 'keterangan' => 'EBS 0010/III/2023', 'nominal' => '220000.00',]);
        saldohutangprediksi::create([ 'nobukti' => 'EBS 0011/III/2023', 'keterangan' => 'EBS 0011/III/2023', 'nominal' => '255000.00',]);
        saldohutangprediksi::create([ 'nobukti' => 'EBS 0012/III/2023', 'keterangan' => 'EBS 0012/III/2023', 'nominal' => '165000.00',]);
        saldohutangprediksi::create([ 'nobukti' => 'EBS 0013/III/2023', 'keterangan' => 'EBS 0013/III/2023', 'nominal' => '265000.00',]);
        saldohutangprediksi::create([ 'nobukti' => 'EBS 0001/IV/2023', 'keterangan' => 'EBS 0001/IV/2023', 'nominal' => '175000.00',]);
        saldohutangprediksi::create([ 'nobukti' => 'EBS 0002/IV/2023', 'keterangan' => 'EBS 0002/IV/2023', 'nominal' => '100000.00',]);
    }
}
