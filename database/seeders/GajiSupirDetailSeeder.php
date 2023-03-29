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
    }
}
