<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SuratPengantarBiayaTambahan;
use Illuminate\Support\Facades\DB;

class SuratPengantarBiayaTambahanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete suratpengantarbiayatambahan");
        DB::statement("DBCC CHECKIDENT ('suratpengantarbiayatambahan', RESEED, 1);");

        suratpengantarbiayatambahan::create([ 'suratpengantar_id' => '64', 'keteranganbiaya' => 'OKP', 'nominal' => '10000', 'nominaltagih' => '0', 'modifiedby' => 'chairunnisa',]);
        suratpengantarbiayatambahan::create([ 'suratpengantar_id' => '735', 'keteranganbiaya' => 'BIAYA MASUK PORTAL KIM (ACC)', 'nominal' => '5000', 'nominaltagih' => '0', 'modifiedby' => 'chairunnisa',]);
        suratpengantarbiayatambahan::create([ 'suratpengantar_id' => '740', 'keteranganbiaya' => 'BIAYA MASUK PORTAL KIM (ACC)', 'nominal' => '5000', 'nominaltagih' => '0', 'modifiedby' => 'chairunnisa',]);
        suratpengantarbiayatambahan::create([ 'suratpengantar_id' => '745', 'keteranganbiaya' => 'BIAYA MASUK PORTAL KIM (ACC)', 'nominal' => '5000', 'nominaltagih' => '0', 'modifiedby' => 'chairunnisa',]);
        suratpengantarbiayatambahan::create([ 'suratpengantar_id' => '803', 'keteranganbiaya' => 'OKP', 'nominal' => '10000', 'nominaltagih' => '0', 'modifiedby' => 'chairunnisa',]);
        suratpengantarbiayatambahan::create([ 'suratpengantar_id' => '823', 'keteranganbiaya' => 'OKP', 'nominal' => '10000', 'nominaltagih' => '0', 'modifiedby' => 'chairunnisa',]);
        suratpengantarbiayatambahan::create([ 'suratpengantar_id' => '878', 'keteranganbiaya' => 'OKP', 'nominal' => '10000', 'nominaltagih' => '0', 'modifiedby' => 'chairunnisa',]);
        suratpengantarbiayatambahan::create([ 'suratpengantar_id' => '950', 'keteranganbiaya' => 'OKP', 'nominal' => '10000', 'nominaltagih' => '0', 'modifiedby' => 'chairunnisa',]);
        suratpengantarbiayatambahan::create([ 'suratpengantar_id' => '1030', 'keteranganbiaya' => 'BIAYA MASUK PORTAL KIM', 'nominal' => '5000', 'nominaltagih' => '0', 'modifiedby' => 'chairunnisa',]);
        suratpengantarbiayatambahan::create([ 'suratpengantar_id' => '1058', 'keteranganbiaya' => 'OKP', 'nominal' => '10000', 'nominaltagih' => '0', 'modifiedby' => 'chairunnisa',]);
        suratpengantarbiayatambahan::create([ 'suratpengantar_id' => '1074', 'keteranganbiaya' => 'PORTAL KIM', 'nominal' => '5000', 'nominaltagih' => '0', 'modifiedby' => 'chairunnisa',]);
        suratpengantarbiayatambahan::create([ 'suratpengantar_id' => '1076', 'keteranganbiaya' => 'BIAYA MASUK PORTAL', 'nominal' => '5000', 'nominaltagih' => '0', 'modifiedby' => 'chairunnisa',]);
        suratpengantarbiayatambahan::create([ 'suratpengantar_id' => '1094', 'keteranganbiaya' => 'OKP', 'nominal' => '10000', 'nominaltagih' => '0', 'modifiedby' => 'chairunnisa',]);
        suratpengantarbiayatambahan::create([ 'suratpengantar_id' => '1096', 'keteranganbiaya' => 'OKP', 'nominal' => '10000', 'nominaltagih' => '0', 'modifiedby' => 'chairunnisa',]);
        suratpengantarbiayatambahan::create([ 'suratpengantar_id' => '1104', 'keteranganbiaya' => 'BIAYA GANTUNG KANDANG (MSI)', 'nominal' => '50000', 'nominaltagih' => '0', 'modifiedby' => 'chairunnisa',]);
        suratpengantarbiayatambahan::create([ 'suratpengantar_id' => '1107', 'keteranganbiaya' => 'BIAYA GANTUNG KANDANG (MSI)', 'nominal' => '50000', 'nominaltagih' => '0', 'modifiedby' => 'chairunnisa',]);
        suratpengantarbiayatambahan::create([ 'suratpengantar_id' => '1131', 'keteranganbiaya' => 'BIAYA PORTAL KIM', 'nominal' => '5000', 'nominaltagih' => '0', 'modifiedby' => 'chairunnisa',]);
        suratpengantarbiayatambahan::create([ 'suratpengantar_id' => '1134', 'keteranganbiaya' => 'PORTAL KIM', 'nominal' => '5000', 'nominaltagih' => '0', 'modifiedby' => 'chairunnisa',]);
        suratpengantarbiayatambahan::create([ 'suratpengantar_id' => '1136', 'keteranganbiaya' => 'PORTAL KIM', 'nominal' => '5000', 'nominaltagih' => '0', 'modifiedby' => 'chairunnisa',]);
        suratpengantarbiayatambahan::create([ 'suratpengantar_id' => '1170', 'keteranganbiaya' => 'PORTAL KIM', 'nominal' => '5000', 'nominaltagih' => '0', 'modifiedby' => 'chairunnisa',]);
        suratpengantarbiayatambahan::create([ 'suratpengantar_id' => '1200', 'keteranganbiaya' => 'PORTAL KIM', 'nominal' => '5000', 'nominaltagih' => '0', 'modifiedby' => 'chairunnisa',]);
        suratpengantarbiayatambahan::create([ 'suratpengantar_id' => '1201', 'keteranganbiaya' => 'PORTAL KIM', 'nominal' => '5000', 'nominaltagih' => '0', 'modifiedby' => 'chairunnisa',]);
        suratpengantarbiayatambahan::create([ 'suratpengantar_id' => '1202', 'keteranganbiaya' => 'PORTAL KIM', 'nominal' => '5000', 'nominaltagih' => '0', 'modifiedby' => 'chairunnisa',]);
        suratpengantarbiayatambahan::create([ 'suratpengantar_id' => '1204', 'keteranganbiaya' => 'BIAYA TEMPEL BAN (ACC KO ASAN)', 'nominal' => '45000', 'nominaltagih' => '0', 'modifiedby' => 'chairunnisa',]);
        suratpengantarbiayatambahan::create([ 'suratpengantar_id' => '1213', 'keteranganbiaya' => 'PORTAL KIM', 'nominal' => '5000', 'nominaltagih' => '0', 'modifiedby' => 'chairunnisa',]);
        suratpengantarbiayatambahan::create([ 'suratpengantar_id' => '1239', 'keteranganbiaya' => 'PORTAL KIM', 'nominal' => '5000', 'nominaltagih' => '0', 'modifiedby' => 'chairunnisa',]);
        suratpengantarbiayatambahan::create([ 'suratpengantar_id' => '1247', 'keteranganbiaya' => 'PORTAL KIM', 'nominal' => '5000', 'nominaltagih' => '0', 'modifiedby' => 'chairunnisa',]);
        suratpengantarbiayatambahan::create([ 'suratpengantar_id' => '1279', 'keteranganbiaya' => 'PORTAL KIM', 'nominal' => '5000', 'nominaltagih' => '0', 'modifiedby' => 'chairunnisa',]);
        suratpengantarbiayatambahan::create([ 'suratpengantar_id' => '1287', 'keteranganbiaya' => 'PORTAL KIM', 'nominal' => '5000', 'nominaltagih' => '0', 'modifiedby' => 'chairunnisa',]);
        suratpengantarbiayatambahan::create([ 'suratpengantar_id' => '1291', 'keteranganbiaya' => 'BIAYA GANTUNG KANDANG (MSI)', 'nominal' => '50000', 'nominaltagih' => '0', 'modifiedby' => 'chairunnisa',]);
        suratpengantarbiayatambahan::create([ 'suratpengantar_id' => '1304', 'keteranganbiaya' => 'BIAYA GANTUNG KANDANG (MSI)', 'nominal' => '50000', 'nominaltagih' => '0', 'modifiedby' => 'chairunnisa',]);
        suratpengantarbiayatambahan::create([ 'suratpengantar_id' => '1318', 'keteranganbiaya' => 'PORTAL KIM', 'nominal' => '5000', 'nominaltagih' => '0', 'modifiedby' => 'chairunnisa',]);
        suratpengantarbiayatambahan::create([ 'suratpengantar_id' => '1448', 'keteranganbiaya' => 'GANTUNG KANDANG (MSI)', 'nominal' => '50000', 'nominaltagih' => '0', 'modifiedby' => 'chairunnisa',]);

    }
}
