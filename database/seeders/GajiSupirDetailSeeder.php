<?php

namespace Database\Seeders;

use App\Models\GajiSupirDetail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GajiSupirDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete GajiSupirDetail");
        DB::statement("DBCC CHECKIDENT ('GajiSupirDetail', RESEED, 1);");

        gajisupirdetail::create(['gajisupir_id' => '1', 'nobukti' => 'RIC 0001/II/2023', 'nominaldeposito' => '0', 'nourut' => '1', 'suratpengantar_nobukti' => 'TRP 0570/I/2023', 'ritasi_nobukti' => 'RTT 0176/I/2023', 'komisisupir' => '0', 'tolsupir' => '0', 'voucher' => '0', 'novoucher' => '0', 'gajisupir' => '113556', 'gajikenek' => '0', 'gajiritasi' => '20400', 'biayatambahan' => '0', 'keteranganbiayatambahan' => '-', 'nominalpengembalianpinjaman' => '0', 'modifiedby' => 'ADMIN',]);
        gajisupirdetail::create(['gajisupir_id' => '1', 'nobukti' => 'RIC 0001/II/2023', 'nominaldeposito' => '0', 'nourut' => '2', 'suratpengantar_nobukti' => 'TRP 0571/I/2023', 'ritasi_nobukti' => '-', 'komisisupir' => '5000', 'tolsupir' => '0', 'voucher' => '0', 'novoucher' => '0', 'gajisupir' => '50000', 'gajikenek' => '0', 'gajiritasi' => '0', 'biayatambahan' => '0', 'keteranganbiayatambahan' => '-', 'nominalpengembalianpinjaman' => '0', 'modifiedby' => 'ADMIN',]);
        gajisupirdetail::create(['gajisupir_id' => '1', 'nobukti' => 'RIC 0001/II/2023', 'nominaldeposito' => '0', 'nourut' => '3', 'suratpengantar_nobukti' => 'TRP 0591/I/2023', 'ritasi_nobukti' => 'RTT 0198/I/2023', 'komisisupir' => '5000', 'tolsupir' => '0', 'voucher' => '0', 'novoucher' => '0', 'gajisupir' => '92933', 'gajikenek' => '0', 'gajiritasi' => '40800', 'biayatambahan' => '0', 'keteranganbiayatambahan' => '-', 'nominalpengembalianpinjaman' => '0', 'modifiedby' => 'ADMIN',]);
        gajisupirdetail::create(['gajisupir_id' => '2', 'nobukti' => 'RIC 0002/II/2023', 'nominaldeposito' => '0', 'nourut' => '1', 'suratpengantar_nobukti' => 'TRP 0560/I/2023', 'ritasi_nobukti' => 'RTT 0173/I/2023', 'komisisupir' => '0', 'tolsupir' => '0', 'voucher' => '0', 'novoucher' => '0', 'gajisupir' => '214399', 'gajikenek' => '0', 'gajiritasi' => '30900', 'biayatambahan' => '0', 'keteranganbiayatambahan' => '-', 'nominalpengembalianpinjaman' => '0', 'modifiedby' => 'ADMIN',]);
        gajisupirdetail::create(['gajisupir_id' => '2', 'nobukti' => 'RIC 0002/II/2023', 'nominaldeposito' => '0', 'nourut' => '2', 'suratpengantar_nobukti' => 'TRP 0585/I/2023', 'ritasi_nobukti' => 'RTT 0187/I/2023', 'komisisupir' => '0', 'tolsupir' => '0', 'voucher' => '0', 'novoucher' => '0', 'gajisupir' => '188692', 'gajikenek' => '0', 'gajiritasi' => '20400', 'biayatambahan' => '0', 'keteranganbiayatambahan' => '-', 'nominalpengembalianpinjaman' => '0', 'modifiedby' => 'ADMIN',]);
        gajisupirdetail::create(['gajisupir_id' => '2', 'nobukti' => 'RIC 0002/II/2023', 'nominaldeposito' => '0', 'nourut' => '3', 'suratpengantar_nobukti' => 'TRP 0586/I/2023', 'ritasi_nobukti' => '-', 'komisisupir' => '10000', 'tolsupir' => '0', 'voucher' => '0', 'novoucher' => '0', 'gajisupir' => '291315', 'gajikenek' => '0', 'gajiritasi' => '0', 'biayatambahan' => '0', 'keteranganbiayatambahan' => '-', 'nominalpengembalianpinjaman' => '0', 'modifiedby' => 'ADMIN',]);
        gajisupirdetail::create(['gajisupir_id' => '3', 'nobukti' => 'RIC 0003/II/2023', 'nominaldeposito' => '0', 'nourut' => '1', 'suratpengantar_nobukti' => 'TRP 0551/I/2023', 'ritasi_nobukti' => 'RTT 0207/I/2023', 'komisisupir' => '0', 'tolsupir' => '0', 'voucher' => '0', 'novoucher' => '0', 'gajisupir' => '73680', 'gajikenek' => '0', 'gajiritasi' => '47800', 'biayatambahan' => '0', 'keteranganbiayatambahan' => '-', 'nominalpengembalianpinjaman' => '0', 'modifiedby' => 'ADMIN',]);
        gajisupirdetail::create(['gajisupir_id' => '3', 'nobukti' => 'RIC 0003/II/2023', 'nominaldeposito' => '0', 'nourut' => '2', 'suratpengantar_nobukti' => 'TRP 0575/I/2023', 'ritasi_nobukti' => '-', 'komisisupir' => '0', 'tolsupir' => '0', 'voucher' => '0', 'novoucher' => '0', 'gajisupir' => '50000', 'gajikenek' => '0', 'gajiritasi' => '0', 'biayatambahan' => '0', 'keteranganbiayatambahan' => '-', 'nominalpengembalianpinjaman' => '0', 'modifiedby' => 'ADMIN',]);
        gajisupirdetail::create(['gajisupir_id' => '3', 'nobukti' => 'RIC 0003/II/2023', 'nominaldeposito' => '0', 'nourut' => '3', 'suratpengantar_nobukti' => 'TRP 0576/I/2023', 'ritasi_nobukti' => '-', 'komisisupir' => '5000', 'tolsupir' => '0', 'voucher' => '0', 'novoucher' => '0', 'gajisupir' => '75000', 'gajikenek' => '0', 'gajiritasi' => '0', 'biayatambahan' => '0', 'keteranganbiayatambahan' => '-', 'nominalpengembalianpinjaman' => '0', 'modifiedby' => 'ADMIN',]);
        gajisupirdetail::create(['gajisupir_id' => '3', 'nobukti' => 'RIC 0003/II/2023', 'nominaldeposito' => '0', 'nourut' => '4', 'suratpengantar_nobukti' => 'TRP 0602/I/2023', 'ritasi_nobukti' => '-', 'komisisupir' => '5000', 'tolsupir' => '0', 'voucher' => '0', 'novoucher' => '0', 'gajisupir' => '72768', 'gajikenek' => '0', 'gajiritasi' => '0', 'biayatambahan' => '0', 'keteranganbiayatambahan' => '-', 'nominalpengembalianpinjaman' => '0', 'modifiedby' => 'ADMIN',]);
        gajisupirdetail::create(['gajisupir_id' => '4', 'nobukti' => 'RIC 0004/II/2023', 'nominaldeposito' => '0', 'nourut' => '1', 'suratpengantar_nobukti' => 'TRP 0543/I/2023', 'ritasi_nobukti' => '-', 'komisisupir' => '5000', 'tolsupir' => '0', 'voucher' => '0', 'novoucher' => '0', 'gajisupir' => '75000', 'gajikenek' => '0', 'gajiritasi' => '0', 'biayatambahan' => '0', 'keteranganbiayatambahan' => '-', 'nominalpengembalianpinjaman' => '0', 'modifiedby' => 'ADMIN',]);
    }
}
