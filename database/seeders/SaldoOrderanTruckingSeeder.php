<?php

namespace Database\Seeders;
use App\Models\SaldoOrderanTrucking;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Seeder;

class SaldoOrderanTruckingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete saldoorderantrucking");
        DB::statement("DBCC CHECKIDENT ('saldoorderantrucking', RESEED, 1);");

        saldoorderantrucking::create(['nobukti' => '0273/IV/23', 'tglbukti' => '2023/4/29', 'container_id' => '2', 'agen_id' => '69', 'jenisorder_id' => '0', 'pelanggan_id' => '2011', 'tarif_id' => '0', 'nominal' => '1828750.00', 'nojobemkl' => '', 'nocont' => 'WHSU 5963123', 'noseal' => '900622', 'nojobemkl2' => '', 'nocont2' => '', 'noseal2' => '', 'statuslangsir' => '80', 'statusperalihan' => '68', 'jobtruckingasal' => '', 'statusapprovalnonchargegandengan' => '0', 'userapprovalnonchargegandengan' => '', 'tglapprovalnonchargegandengan' => '1900/1/1', 'statusapprovalbukatrip' => '0', 'tglapprovalbukatrip' => '1900/1/1', 'userapprovalbukatrip' => '', 'statusformat' => '103', 'modifiedby' => 'chairunnisa',]);
        saldoorderantrucking::create(['nobukti' => '0278/IV/23', 'tglbukti' => '2023/4/30', 'container_id' => '1', 'agen_id' => '64', 'jenisorder_id' => '1', 'pelanggan_id' => '624', 'tarif_id' => '0', 'nominal' => '0.00', 'nojobemkl' => 'PRE3/V/KTR - MD/DMI-LCL/23', 'nocont' => 'PRE3/V/KTR - MD/DMI-LCL/23', 'noseal' => 'LCL', 'nojobemkl2' => '', 'nocont2' => '', 'noseal2' => '', 'statuslangsir' => '80', 'statusperalihan' => '68', 'jobtruckingasal' => '', 'statusapprovalnonchargegandengan' => '0', 'userapprovalnonchargegandengan' => '', 'tglapprovalnonchargegandengan' => '1900/1/1', 'statusapprovalbukatrip' => '0', 'tglapprovalbukatrip' => '1900/1/1', 'userapprovalbukatrip' => '', 'statusformat' => '103', 'modifiedby' => 'chairunnisa',]);
        saldoorderantrucking::create(['nobukti' => '0001/V/23', 'tglbukti' => '2023/5/1', 'container_id' => '3', 'agen_id' => '64', 'jenisorder_id' => '2', 'pelanggan_id' => '2047', 'tarif_id' => '0', 'nominal' => '0.00', 'nojobemkl' => '103/IV/JKT/23', 'nocont' => 'TCKU 1932812', 'noseal' => '0759392', 'nojobemkl2' => '101/IV/JKT/23', 'nocont2' => 'TRHU 1327677', 'noseal2' => '0759391', 'statuslangsir' => '80', 'statusperalihan' => '68', 'jobtruckingasal' => '', 'statusapprovalnonchargegandengan' => '0', 'userapprovalnonchargegandengan' => '', 'tglapprovalnonchargegandengan' => '1900/1/1', 'statusapprovalbukatrip' => '0', 'tglapprovalbukatrip' => '1900/1/1', 'userapprovalbukatrip' => '', 'statusformat' => '103', 'modifiedby' => 'chairunnisa',]);
        saldoorderantrucking::create(['nobukti' => '0002/V/23', 'tglbukti' => '2023/5/1', 'container_id' => '1', 'agen_id' => '64', 'jenisorder_id' => '1', 'pelanggan_id' => '624', 'tarif_id' => '0', 'nominal' => '0.00', 'nojobemkl' => 'PRE2/V/KTR - MD/DMI-LCL/23', 'nocont' => 'PRE2/V/KTR - MD/DMI-LCL/23', 'noseal' => 'LCL', 'nojobemkl2' => '', 'nocont2' => '', 'noseal2' => '', 'statuslangsir' => '80', 'statusperalihan' => '68', 'jobtruckingasal' => '', 'statusapprovalnonchargegandengan' => '0', 'userapprovalnonchargegandengan' => '', 'tglapprovalnonchargegandengan' => '1900/1/1', 'statusapprovalbukatrip' => '0', 'tglapprovalbukatrip' => '1900/1/1', 'userapprovalbukatrip' => '', 'statusformat' => '103', 'modifiedby' => 'chairunnisa',]);
        saldoorderantrucking::create(['nobukti' => '0003/V/23', 'tglbukti' => '2023/5/1', 'container_id' => '3', 'agen_id' => '64', 'jenisorder_id' => '2', 'pelanggan_id' => '2047', 'tarif_id' => '0', 'nominal' => '0.00', 'nojobemkl' => '107/IV/JKT/23', 'nocont' => 'ZIMU 2873081', 'noseal' => '0759395', 'nojobemkl2' => '108/IV/JKT/23', 'nocont2' => 'ZIMU 1024794', 'noseal2' => '0759396', 'statuslangsir' => '80', 'statusperalihan' => '68', 'jobtruckingasal' => '', 'statusapprovalnonchargegandengan' => '0', 'userapprovalnonchargegandengan' => '', 'tglapprovalnonchargegandengan' => '1900/1/1', 'statusapprovalbukatrip' => '0', 'tglapprovalbukatrip' => '1900/1/1', 'userapprovalbukatrip' => '', 'statusformat' => '103', 'modifiedby' => 'chairunnisa',]);
        saldoorderantrucking::create(['nobukti' => '0006/V/23', 'tglbukti' => '2023/5/1', 'container_id' => '1', 'agen_id' => '64', 'jenisorder_id' => '1', 'pelanggan_id' => '2144', 'tarif_id' => '0', 'nominal' => '0.00', 'nojobemkl' => 'PRE1/V/KTR - MD/SBY/23', 'nocont' => 'TEGU 3037451', 'noseal' => '2265257 / 0020886', 'nojobemkl2' => '', 'nocont2' => '', 'noseal2' => '', 'statuslangsir' => '80', 'statusperalihan' => '68', 'jobtruckingasal' => '', 'statusapprovalnonchargegandengan' => '0', 'userapprovalnonchargegandengan' => '', 'tglapprovalnonchargegandengan' => '1900/1/1', 'statusapprovalbukatrip' => '0', 'tglapprovalbukatrip' => '1900/1/1', 'userapprovalbukatrip' => '', 'statusformat' => '103', 'modifiedby' => 'chairunnisa',]);
        saldoorderantrucking::create(['nobukti' => '0007/V/23', 'tglbukti' => '2023/5/1', 'container_id' => '3', 'agen_id' => '64', 'jenisorder_id' => '2', 'pelanggan_id' => '2047', 'tarif_id' => '0', 'nominal' => '0.00', 'nojobemkl' => '105/IV/JKT/23', 'nocont' => 'OOLU 1534899', 'noseal' => '0759393', 'nojobemkl2' => '106/IV/JKT/23', 'nocont2' => 'YMLU 3587214', 'noseal2' => '0759394', 'statuslangsir' => '80', 'statusperalihan' => '68', 'jobtruckingasal' => '', 'statusapprovalnonchargegandengan' => '0', 'userapprovalnonchargegandengan' => '', 'tglapprovalnonchargegandengan' => '1900/1/1', 'statusapprovalbukatrip' => '0', 'tglapprovalbukatrip' => '1900/1/1', 'userapprovalbukatrip' => '', 'statusformat' => '103', 'modifiedby' => 'chairunnisa',]);
        saldoorderantrucking::create(['nobukti' => '0008/V/23', 'tglbukti' => '2023/5/1', 'container_id' => '1', 'agen_id' => '64', 'jenisorder_id' => '1', 'pelanggan_id' => '2144', 'tarif_id' => '0', 'nominal' => '0.00', 'nojobemkl' => 'PRE2/V/KTR - MD/SBY/23', 'nocont' => 'TEGU 7031533', 'noseal' => '2265258 / 0020887', 'nojobemkl2' => '', 'nocont2' => '', 'noseal2' => '', 'statuslangsir' => '80', 'statusperalihan' => '68', 'jobtruckingasal' => '', 'statusapprovalnonchargegandengan' => '0', 'userapprovalnonchargegandengan' => '', 'tglapprovalnonchargegandengan' => '1900/1/1', 'statusapprovalbukatrip' => '0', 'tglapprovalbukatrip' => '1900/1/1', 'userapprovalbukatrip' => '', 'statusformat' => '103', 'modifiedby' => 'chairunnisa',]);
        saldoorderantrucking::create(['nobukti' => '0010/V/23', 'tglbukti' => '2023/5/2', 'container_id' => '3', 'agen_id' => '64', 'jenisorder_id' => '3', 'pelanggan_id' => '1857', 'tarif_id' => '0', 'nominal' => '1830000.00', 'nojobemkl' => '12/IV/KTR - MD/IMPORT/23', 'nocont' => 'TLLU2031080', 'noseal' => '', 'nojobemkl2' => '12/IV/KTR - MD/IMPORT/23', 'nocont2' => 'TRHU3782303', 'noseal2' => '', 'statuslangsir' => '80', 'statusperalihan' => '68', 'jobtruckingasal' => '', 'statusapprovalnonchargegandengan' => '0', 'userapprovalnonchargegandengan' => '', 'tglapprovalnonchargegandengan' => '1900/1/1', 'statusapprovalbukatrip' => '0', 'tglapprovalbukatrip' => '1900/1/1', 'userapprovalbukatrip' => '', 'statusformat' => '103', 'modifiedby' => 'chairunnisa',]);
        saldoorderantrucking::create(['nobukti' => '0263/IV/23', 'tglbukti' => '2023/4/28', 'container_id' => '1', 'agen_id' => '64', 'jenisorder_id' => '1', 'pelanggan_id' => '2094', 'tarif_id' => '0', 'nominal' => '1117000.00', 'nojobemkl' => 'PRE48/IV/KTR - MD/JKT/23', 'nocont' => 'TEGU 3072935', 'noseal' => '2265425 / 0020879', 'nojobemkl2' => '', 'nocont2' => '', 'noseal2' => '', 'statuslangsir' => '80', 'statusperalihan' => '68', 'jobtruckingasal' => '', 'statusapprovalnonchargegandengan' => '0', 'userapprovalnonchargegandengan' => '', 'tglapprovalnonchargegandengan' => '1900/1/1', 'statusapprovalbukatrip' => '0', 'tglapprovalbukatrip' => '1900/1/1', 'userapprovalbukatrip' => '', 'statusformat' => '103', 'modifiedby' => 'chairunnisa',]);
        saldoorderantrucking::create(['nobukti' => '0266/IV/23', 'tglbukti' => '2023/4/29', 'container_id' => '2', 'agen_id' => '69', 'jenisorder_id' => '0', 'pelanggan_id' => '2011', 'tarif_id' => '0', 'nominal' => '1828750.00', 'nojobemkl' => '', 'nocont' => 'WHSU 6548174', 'noseal' => '900621', 'nojobemkl2' => '', 'nocont2' => '', 'noseal2' => '', 'statuslangsir' => '80', 'statusperalihan' => '68', 'jobtruckingasal' => '', 'statusapprovalnonchargegandengan' => '0', 'userapprovalnonchargegandengan' => '', 'tglapprovalnonchargegandengan' => '1900/1/1', 'statusapprovalbukatrip' => '0', 'tglapprovalbukatrip' => '1900/1/1', 'userapprovalbukatrip' => '', 'statusformat' => '103', 'modifiedby' => 'chairunnisa',]);
        saldoorderantrucking::create(['nobukti' => '0268/IV/23', 'tglbukti' => '2023/4/29', 'container_id' => '2', 'agen_id' => '69', 'jenisorder_id' => '0', 'pelanggan_id' => '2011', 'tarif_id' => '0', 'nominal' => '1828750.00', 'nojobemkl' => '', 'nocont' => 'DFSU 7624620', 'noseal' => '900624', 'nojobemkl2' => '', 'nocont2' => '', 'noseal2' => '', 'statuslangsir' => '80', 'statusperalihan' => '68', 'jobtruckingasal' => '', 'statusapprovalnonchargegandengan' => '0', 'userapprovalnonchargegandengan' => '', 'tglapprovalnonchargegandengan' => '1900/1/1', 'statusapprovalbukatrip' => '0', 'tglapprovalbukatrip' => '1900/1/1', 'userapprovalbukatrip' => '', 'statusformat' => '103', 'modifiedby' => 'chairunnisa',]);
        saldoorderantrucking::create(['nobukti' => '0274/IV/23', 'tglbukti' => '2023/4/29', 'container_id' => '2', 'agen_id' => '69', 'jenisorder_id' => '0', 'pelanggan_id' => '2011', 'tarif_id' => '0', 'nominal' => '1828750.00', 'nojobemkl' => '', 'nocont' => 'WHSU 5882612', 'noseal' => '900623', 'nojobemkl2' => '', 'nocont2' => '', 'noseal2' => '', 'statuslangsir' => '80', 'statusperalihan' => '68', 'jobtruckingasal' => '', 'statusapprovalnonchargegandengan' => '0', 'userapprovalnonchargegandengan' => '', 'tglapprovalnonchargegandengan' => '1900/1/1', 'statusapprovalbukatrip' => '0', 'tglapprovalbukatrip' => '1900/1/1', 'userapprovalbukatrip' => '', 'statusformat' => '103', 'modifiedby' => 'chairunnisa',]);
        saldoorderantrucking::create(['nobukti' => '0004/V/23', 'tglbukti' => '2023/5/1', 'container_id' => '1', 'agen_id' => '64', 'jenisorder_id' => '1', 'pelanggan_id' => '1428', 'tarif_id' => '0', 'nominal' => '1560000.00', 'nojobemkl' => 'PRE52/IV/KTR - MD/JKT/23', 'nocont' => 'MRLU 2374253', 'noseal' => 'H 310118 / 0020888', 'nojobemkl2' => '', 'nocont2' => '', 'noseal2' => '', 'statuslangsir' => '80', 'statusperalihan' => '68', 'jobtruckingasal' => '', 'statusapprovalnonchargegandengan' => '0', 'userapprovalnonchargegandengan' => '', 'tglapprovalnonchargegandengan' => '1900/1/1', 'statusapprovalbukatrip' => '0', 'tglapprovalbukatrip' => '1900/1/1', 'userapprovalbukatrip' => '', 'statusformat' => '103', 'modifiedby' => 'chairunnisa',]);
        saldoorderantrucking::create(['nobukti' => '0005/V/23', 'tglbukti' => '2023/5/1', 'container_id' => '1', 'agen_id' => '64', 'jenisorder_id' => '1', 'pelanggan_id' => '1428', 'tarif_id' => '0', 'nominal' => '1560000.00', 'nojobemkl' => 'PRE51/IV/KTR - MD/JKT/23', 'nocont' => 'MRLU 2356521', 'noseal' => 'H 310117 / 0020884', 'nojobemkl2' => '', 'nocont2' => '', 'noseal2' => '', 'statuslangsir' => '80', 'statusperalihan' => '68', 'jobtruckingasal' => '', 'statusapprovalnonchargegandengan' => '0', 'userapprovalnonchargegandengan' => '', 'tglapprovalnonchargegandengan' => '1900/1/1', 'statusapprovalbukatrip' => '0', 'tglapprovalbukatrip' => '1900/1/1', 'userapprovalbukatrip' => '', 'statusformat' => '103', 'modifiedby' => 'chairunnisa',]);
        saldoorderantrucking::create(['nobukti' => '0009/V/23', 'tglbukti' => '2023/5/1', 'container_id' => '1', 'agen_id' => '64', 'jenisorder_id' => '1', 'pelanggan_id' => '184', 'tarif_id' => '0', 'nominal' => '0.00', 'nojobemkl' => 'PRE53/IV/KTR - MD/JKT/23', 'nocont' => 'SDDU 2008199', 'noseal' => '2265256 / 0020885', 'nojobemkl2' => '', 'nocont2' => '', 'noseal2' => '', 'statuslangsir' => '80', 'statusperalihan' => '68', 'jobtruckingasal' => '', 'statusapprovalnonchargegandengan' => '0', 'userapprovalnonchargegandengan' => '', 'tglapprovalnonchargegandengan' => '1900/1/1', 'statusapprovalbukatrip' => '0', 'tglapprovalbukatrip' => '1900/1/1', 'userapprovalbukatrip' => '', 'statusformat' => '103', 'modifiedby' => 'chairunnisa',]);
        saldoorderantrucking::create(['nobukti' => '0011/V/23', 'tglbukti' => '2023/5/2', 'container_id' => '1', 'agen_id' => '64', 'jenisorder_id' => '3', 'pelanggan_id' => '1857', 'tarif_id' => '0', 'nominal' => '1117000.00', 'nojobemkl' => '12/IV/KTR - MD/IMPORT/23', 'nocont' => 'NYKU9905025', 'noseal' => '', 'nojobemkl2' => '', 'nocont2' => '', 'noseal2' => '', 'statuslangsir' => '80', 'statusperalihan' => '68', 'jobtruckingasal' => '', 'statusapprovalnonchargegandengan' => '0', 'userapprovalnonchargegandengan' => '', 'tglapprovalnonchargegandengan' => '1900/1/1', 'statusapprovalbukatrip' => '0', 'tglapprovalbukatrip' => '1900/1/1', 'userapprovalbukatrip' => '', 'statusformat' => '103', 'modifiedby' => 'chairunnisa',]);
        saldoorderantrucking::create(['nobukti' => '0210/IV/23', 'tglbukti' => '2023/4/15', 'container_id' => '1', 'agen_id' => '64', 'jenisorder_id' => '1', 'pelanggan_id' => '2094', 'tarif_id' => '0', 'nominal' => '1117000.00', 'nojobemkl' => 'PRE39/IV/KTR - MD/JKT/23', 'nocont' => 'TEGU 3087468', 'noseal' => '2265167 / 0020862', 'nojobemkl2' => '', 'nocont2' => '', 'noseal2' => '', 'statuslangsir' => '80', 'statusperalihan' => '68', 'jobtruckingasal' => '', 'statusapprovalnonchargegandengan' => '0', 'userapprovalnonchargegandengan' => '', 'tglapprovalnonchargegandengan' => '1900/1/1', 'statusapprovalbukatrip' => '0', 'tglapprovalbukatrip' => '1900/1/1', 'userapprovalbukatrip' => '', 'statusformat' => '103', 'modifiedby' => 'chairunnisa',]);
        saldoorderantrucking::create(['nobukti' => '0267/IV/23', 'tglbukti' => '2023/4/29', 'container_id' => '2', 'agen_id' => '64', 'jenisorder_id' => '1', 'pelanggan_id' => '1510', 'tarif_id' => '0', 'nominal' => '0.00', 'nojobemkl' => 'PRE2/IV/BOY - MD/BJRMS/23', 'nocont' => 'MRTU 9629611', 'noseal' => 'H 3100115 / 0020882', 'nojobemkl2' => '', 'nocont2' => '', 'noseal2' => '', 'statuslangsir' => '80', 'statusperalihan' => '68', 'jobtruckingasal' => '', 'statusapprovalnonchargegandengan' => '0', 'userapprovalnonchargegandengan' => '', 'tglapprovalnonchargegandengan' => '1900/1/1', 'statusapprovalbukatrip' => '0', 'tglapprovalbukatrip' => '1900/1/1', 'userapprovalbukatrip' => '', 'statusformat' => '103', 'modifiedby' => 'chairunnisa',]);
        saldoorderantrucking::create(['nobukti' => '0271/IV/23', 'tglbukti' => '2023/4/29', 'container_id' => '2', 'agen_id' => '69', 'jenisorder_id' => '0', 'pelanggan_id' => '2011', 'tarif_id' => '0', 'nominal' => '1828750.00', 'nojobemkl' => '', 'nocont' => 'WHSU 6357033', 'noseal' => '900625', 'nojobemkl2' => '', 'nocont2' => '', 'noseal2' => '', 'statuslangsir' => '80', 'statusperalihan' => '68', 'jobtruckingasal' => '', 'statusapprovalnonchargegandengan' => '0', 'userapprovalnonchargegandengan' => '', 'tglapprovalnonchargegandengan' => '1900/1/1', 'statusapprovalbukatrip' => '0', 'tglapprovalbukatrip' => '1900/1/1', 'userapprovalbukatrip' => '', 'statusformat' => '103', 'modifiedby' => 'chairunnisa',]);
        saldoorderantrucking::create(['nobukti' => '0242/IV/23', 'tglbukti' => '2023/4/19', 'container_id' => '3', 'agen_id' => '64', 'jenisorder_id' => '3', 'pelanggan_id' => '129', 'tarif_id' => '0', 'nominal' => '0.00', 'nojobemkl' => '4/IV/KTR - MD/IMPORT/23', 'nocont' => 'GCXU2208104', 'noseal' => '', 'nojobemkl2' => '4/IV/KTR - MD/IMPORT/23', 'nocont2' => 'FCIU5657997', 'noseal2' => '', 'statuslangsir' => '80', 'statusperalihan' => '68', 'jobtruckingasal' => '', 'statusapprovalnonchargegandengan' => '0', 'userapprovalnonchargegandengan' => '', 'tglapprovalnonchargegandengan' => '1900/1/1', 'statusapprovalbukatrip' => '0', 'tglapprovalbukatrip' => '1900/1/1', 'userapprovalbukatrip' => '', 'statusformat' => '103', 'modifiedby' => 'chairunnisa',]);
        saldoorderantrucking::create(['nobukti' => '0269/IV/23', 'tglbukti' => '2023/4/29', 'container_id' => '1', 'agen_id' => '64', 'jenisorder_id' => '2', 'pelanggan_id' => '426', 'tarif_id' => '0', 'nominal' => '0.00', 'nojobemkl' => '102/IV/JKT/23', 'nocont' => 'ICKU 3002882', 'noseal' => '0759400', 'nojobemkl2' => '', 'nocont2' => '', 'noseal2' => '', 'statuslangsir' => '80', 'statusperalihan' => '68', 'jobtruckingasal' => '', 'statusapprovalnonchargegandengan' => '0', 'userapprovalnonchargegandengan' => '', 'tglapprovalnonchargegandengan' => '1900/1/1', 'statusapprovalbukatrip' => '0', 'tglapprovalbukatrip' => '1900/1/1', 'userapprovalbukatrip' => '', 'statusformat' => '103', 'modifiedby' => 'chairunnisa',]);
    }
}