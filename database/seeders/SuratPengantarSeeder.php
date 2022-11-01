<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SuratPengantar;
use Illuminate\Support\Facades\DB;

class SuratPengantarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete SuratPengantar");
        DB::statement("DBCC CHECKIDENT ('SuratPengantar', RESEED, 1);");
    
        SuratPengantar::create([ 'nobukti' => 'TRP 0001/III/2022', 'jobtrucking' => '0001/III/22', 'tglbukti' => '2022/3/2', 'pelanggan_id' => '1', 'keterangan' => '-', 'nourutorder' => '1', 'upah_id' => '1', 'dari_id' => '1', 'sampai_id' => '2', 'container_id' => '1', 'nocont' => 'SPNU 123456', 'nocont2' => '', 'statuscontainer_id' => '1', 'trado_id' => '1', 'supir_id' => '1', 'nojob' => 'PRE1/II/KTR - MD/JKT/22', 'nojob2' => '', 'statuslongtrip' => '66', 'omset' => '1021000', 'discount' => '0', 'totalomset' => '1021000', 'gajisupir' => '134760', 'gajikenek' => '15000', 'agen_id' => '2', 'jenisorder_id' => '1', 'statusperalihan' => '68', 'tarif_id' => '1', 'nominalperalihan' => '0', 'persentaseperalihan' => '0', 'biayatambahan_id' => '0', 'nosp' => '2022008076', 'tglsp' => '2022/3/2', 'statusritasiomset' => '70', 'cabang_id' => '3', 'komisisupir' => '0', 'tolsupir' => '0', 'jarak' => '33', 'nosptagihlain' => '', 'nilaitagihlain' => '0', 'tujuantagih' => '', 'liter' => '0', 'nominalstafle' => '0', 'statusnotif' => '72', 'statusoneway' => '74', 'statusedittujuan' => '76', 'upahbongkardepo' => '0', 'upahmuatdepo' => '0', 'hargatol' => '0', 'qtyton' => '0', 'totalton' => '0', 'mandorsupir_id' => '1', 'mandortrado_id' => '2', 'statustrip' => '78', 'notripasal' => '', 'tgldoor' => '1900/1/1', 'statusdisc' => '0', 'statusformat' => '64', 'modifiedby' => 'ADMIN',]);

        SuratPengantar::create([ 'nobukti' => 'TRP 0001/X/2022', 'jobtrucking' => '0001/X/22', 'tglbukti' => '2022/10/15', 'pelanggan_id' => '1', 'keterangan' => '-', 'nourutorder' => '1', 'upah_id' => '1', 'dari_id' => '1', 'sampai_id' => '2', 'container_id' => '1', 'nocont' => 'SPNU 123456', 'nocont2' => '', 'statuscontainer_id' => '1', 'trado_id' => '1', 'supir_id' => '1', 'nojob' => 'PRE1/II/KTR - MD/JKT/22', 'nojob2' => '', 'statuslongtrip' => '66', 'omset' => '1021000', 'discount' => '0', 'totalomset' => '1021000', 'gajisupir' => '134760', 'gajikenek' => '15000', 'agen_id' => '2', 'jenisorder_id' => '1', 'statusperalihan' => '68', 'tarif_id' => '1', 'nominalperalihan' => '0', 'persentaseperalihan' => '0', 'biayatambahan_id' => '0', 'nosp' => '2022010070', 'tglsp' => '2022/10/15', 'statusritasiomset' => '70', 'cabang_id' => '3', 'komisisupir' => '0', 'tolsupir' => '0', 'jarak' => '33', 'nosptagihlain' => '', 'nilaitagihlain' => '0', 'tujuantagih' => '', 'liter' => '0', 'nominalstafle' => '0', 'statusnotif' => '72', 'statusoneway' => '74', 'statusedittujuan' => '76', 'upahbongkardepo' => '0', 'upahmuatdepo' => '0', 'hargatol' => '0', 'qtyton' => '0', 'totalton' => '0', 'mandorsupir_id' => '1', 'mandortrado_id' => '2', 'statustrip' => '78', 'notripasal' => '', 'tgldoor' => '1900/1/1', 'statusdisc' => '0', 'statusformat' => '64', 'modifiedby' => 'ADMIN',]);
        
        SuratPengantar::create([ 'nobukti' => 'TRP 0002/X/2022', 'jobtrucking' => '0001/X/22', 'tglbukti' => '2022/10/15', 'pelanggan_id' => '1', 'keterangan' => '-', 'nourutorder' => '2', 'upah_id' => '1', 'dari_id' => '1', 'sampai_id' => '3', 'container_id' => '1', 'nocont' => 'SPNU 324566', 'nocont2' => '', 'statuscontainer_id' => '1', 'trado_id' => '1', 'supir_id' => '1', 'nojob' => 'PRE1/II/KTR - MD/JKT/22', 'nojob2' => '', 'statuslongtrip' => '66', 'omset' => '1021000', 'discount' => '0', 'totalomset' => '1021000', 'gajisupir' => '134760', 'gajikenek' => '15000', 'agen_id' => '2', 'jenisorder_id' => '1', 'statusperalihan' => '68', 'tarif_id' => '1', 'nominalperalihan' => '0', 'persentaseperalihan' => '0', 'biayatambahan_id' => '0', 'nosp' => '2022010071', 'tglsp' => '2022/10/15', 'statusritasiomset' => '70', 'cabang_id' => '3', 'komisisupir' => '0', 'tolsupir' => '0', 'jarak' => '33', 'nosptagihlain' => '', 'nilaitagihlain' => '0', 'tujuantagih' => '', 'liter' => '0', 'nominalstafle' => '0', 'statusnotif' => '72', 'statusoneway' => '74', 'statusedittujuan' => '76', 'upahbongkardepo' => '0', 'upahmuatdepo' => '0', 'hargatol' => '0', 'qtyton' => '0', 'totalton' => '0', 'mandorsupir_id' => '1', 'mandortrado_id' => '2', 'statustrip' => '78', 'notripasal' => '', 'tgldoor' => '1900/1/1', 'statusdisc' => '0', 'statusformat' => '64', 'modifiedby' => 'ADMIN',]);
         
        SuratPengantar::create([ 'nobukti' => 'TRP 0003/X/2022', 'jobtrucking' => '0002/X/22', 'tglbukti' => '2022/10/17', 'pelanggan_id' => '1', 'keterangan' => '-', 'nourutorder' => '1', 'upah_id' => '1', 'dari_id' => '1', 'sampai_id' => '4', 'container_id' => '1', 'nocont' => 'SPNU 698745', 'nocont2' => '', 'statuscontainer_id' => '1', 'trado_id' => '1', 'supir_id' => '1', 'nojob' => 'PRE1/II/KTR - MD/JKT/22', 'nojob2' => '', 'statuslongtrip' => '66', 'omset' => '1850000', 'discount' => '0', 'totalomset' => '1850000', 'gajisupir' => '134760', 'gajikenek' => '15000', 'agen_id' => '1', 'jenisorder_id' => '2', 'statusperalihan' => '67', 'tarif_id' => '1', 'nominalperalihan' => '0', 'persentaseperalihan' => '0', 'biayatambahan_id' => '0', 'nosp' => '2022010072', 'tglsp' => '2022/10/17', 'statusritasiomset' => '70', 'cabang_id' => '3', 'komisisupir' => '0', 'tolsupir' => '0', 'jarak' => '33', 'nosptagihlain' => '', 'nilaitagihlain' => '0', 'tujuantagih' => '', 'liter' => '0', 'nominalstafle' => '0', 'statusnotif' => '72', 'statusoneway' => '74', 'statusedittujuan' => '76', 'upahbongkardepo' => '0', 'upahmuatdepo' => '0', 'hargatol' => '0', 'qtyton' => '0', 'totalton' => '0', 'mandorsupir_id' => '1', 'mandortrado_id' => '2', 'statustrip' => '78', 'notripasal' => '', 'tgldoor' => '1900/1/1', 'statusdisc' => '0', 'statusformat' => '64', 'modifiedby' => 'ADMIN',]);
     
        SuratPengantar::create([ 'nobukti' => 'TRP 0004/X/2022', 'jobtrucking' => '0003/X/22', 'tglbukti' => '2022/10/17', 'pelanggan_id' => '1', 'keterangan' => '-', 'nourutorder' => '1', 'upah_id' => '1', 'dari_id' => '1', 'sampai_id' => '5', 'container_id' => '1', 'nocont' => 'SPNU 145786', 'nocont2' => '', 'statuscontainer_id' => '1', 'trado_id' => '1', 'supir_id' => '2', 'nojob' => 'PRE1/II/KTR - MD/JKT/22', 'nojob2' => '', 'statuslongtrip' => '65', 'omset' => '1350000', 'discount' => '0', 'totalomset' => '1350000', 'gajisupir' => '134760', 'gajikenek' => '15000', 'agen_id' => '2', 'jenisorder_id' => '2', 'statusperalihan' => '68', 'tarif_id' => '1', 'nominalperalihan' => '0', 'persentaseperalihan' => '0', 'biayatambahan_id' => '0', 'nosp' => '2022010073', 'tglsp' => '2022/10/17', 'statusritasiomset' => '70', 'cabang_id' => '3', 'komisisupir' => '0', 'tolsupir' => '0', 'jarak' => '33', 'nosptagihlain' => '', 'nilaitagihlain' => '0', 'tujuantagih' => '', 'liter' => '0', 'nominalstafle' => '0', 'statusnotif' => '72', 'statusoneway' => '74', 'statusedittujuan' => '76', 'upahbongkardepo' => '0', 'upahmuatdepo' => '0', 'hargatol' => '0', 'qtyton' => '0', 'totalton' => '0', 'mandorsupir_id' => '1', 'mandortrado_id' => '2', 'statustrip' => '78', 'notripasal' => '', 'tgldoor' => '1900/1/1', 'statusdisc' => '0', 'statusformat' => '64', 'modifiedby' => 'ADMIN',]);
    
        SuratPengantar::create([ 'nobukti' => 'TRP 0005/X/2022', 'jobtrucking' => '0004/X/22', 'tglbukti' => '2022/10/18', 'pelanggan_id' => '1', 'keterangan' => '-', 'nourutorder' => '1', 'upah_id' => '1', 'dari_id' => '1', 'sampai_id' => '2', 'container_id' => '1', 'nocont' => 'SPNU 324165', 'nocont2' => '', 'statuscontainer_id' => '1', 'trado_id' => '1', 'supir_id' => '1', 'nojob' => 'PRE1/II/KTR - MD/JKT/22', 'nojob2' => '', 'statuslongtrip' => '66', 'omset' => '1021000', 'discount' => '0', 'totalomset' => '1021000', 'gajisupir' => '134760', 'gajikenek' => '15000', 'agen_id' => '2', 'jenisorder_id' => '1', 'statusperalihan' => '68', 'tarif_id' => '1', 'nominalperalihan' => '0', 'persentaseperalihan' => '0', 'biayatambahan_id' => '0', 'nosp' => '2022010074', 'tglsp' => '2022/10/18', 'statusritasiomset' => '70', 'cabang_id' => '3', 'komisisupir' => '0', 'tolsupir' => '0', 'jarak' => '33', 'nosptagihlain' => '', 'nilaitagihlain' => '0', 'tujuantagih' => '', 'liter' => '0', 'nominalstafle' => '0', 'statusnotif' => '72', 'statusoneway' => '74', 'statusedittujuan' => '76', 'upahbongkardepo' => '0', 'upahmuatdepo' => '0', 'hargatol' => '0', 'qtyton' => '0', 'totalton' => '0', 'mandorsupir_id' => '1', 'mandortrado_id' => '2', 'statustrip' => '78', 'notripasal' => '', 'tgldoor' => '1900/1/1', 'statusdisc' => '0', 'statusformat' => '64', 'modifiedby' => 'ADMIN',]);
        
        SuratPengantar::create([ 'nobukti' => 'TRP 0006/X/2022', 'jobtrucking' => '0004/X/22', 'tglbukti' => '2022/10/18', 'pelanggan_id' => '1', 'keterangan' => '-', 'nourutorder' => '1', 'upah_id' => '1', 'dari_id' => '1', 'sampai_id' => '3', 'container_id' => '1', 'nocont' => 'SPNU 65871', 'nocont2' => '', 'statuscontainer_id' => '1', 'trado_id' => '1', 'supir_id' => '1', 'nojob' => 'PRE1/II/KTR - MD/JKT/22', 'nojob2' => '', 'statuslongtrip' => '66', 'omset' => '1021000', 'discount' => '0', 'totalomset' => '1021000', 'gajisupir' => '134760', 'gajikenek' => '15000', 'agen_id' => '2', 'jenisorder_id' => '1', 'statusperalihan' => '68', 'tarif_id' => '1', 'nominalperalihan' => '0', 'persentaseperalihan' => '0', 'biayatambahan_id' => '0', 'nosp' => '2022010075', 'tglsp' => '2022/10/18', 'statusritasiomset' => '70', 'cabang_id' => '3', 'komisisupir' => '0', 'tolsupir' => '0', 'jarak' => '33', 'nosptagihlain' => '', 'nilaitagihlain' => '0', 'tujuantagih' => '', 'liter' => '0', 'nominalstafle' => '0', 'statusnotif' => '72', 'statusoneway' => '74', 'statusedittujuan' => '76', 'upahbongkardepo' => '0', 'upahmuatdepo' => '0', 'hargatol' => '0', 'qtyton' => '0', 'totalton' => '0', 'mandorsupir_id' => '1', 'mandortrado_id' => '2', 'statustrip' => '78', 'notripasal' => '', 'tgldoor' => '1900/1/1', 'statusdisc' => '0', 'statusformat' => '64', 'modifiedby' => 'ADMIN',]);

        SuratPengantar::create([ 'nobukti' => 'TRP 0007/X/2022', 'jobtrucking' => '0004/X/22', 'tglbukti' => '2022/10/18', 'pelanggan_id' => '1', 'keterangan' => '-', 'nourutorder' => '1', 'upah_id' => '1', 'dari_id' => '3', 'sampai_id' => '1', 'container_id' => '1', 'nocont' => 'SPNU 741235', 'nocont2' => '', 'statuscontainer_id' => '1', 'trado_id' => '1', 'supir_id' => '1', 'nojob' => 'PRE1/II/KTR - MD/JKT/22', 'nojob2' => '', 'statuslongtrip' => '66', 'omset' => '1021000', 'discount' => '0', 'totalomset' => '1021000', 'gajisupir' => '134760', 'gajikenek' => '15000', 'agen_id' => '2', 'jenisorder_id' => '2', 'statusperalihan' => '68', 'tarif_id' => '1', 'nominalperalihan' => '0', 'persentaseperalihan' => '0', 'biayatambahan_id' => '0', 'nosp' => '2022010076', 'tglsp' => '2022/10/18', 'statusritasiomset' => '70', 'cabang_id' => '3', 'komisisupir' => '0', 'tolsupir' => '0', 'jarak' => '33', 'nosptagihlain' => '', 'nilaitagihlain' => '0', 'tujuantagih' => '', 'liter' => '0', 'nominalstafle' => '0', 'statusnotif' => '72', 'statusoneway' => '74', 'statusedittujuan' => '76', 'upahbongkardepo' => '0', 'upahmuatdepo' => '0', 'hargatol' => '0', 'qtyton' => '0', 'totalton' => '0', 'mandorsupir_id' => '1', 'mandortrado_id' => '2', 'statustrip' => '78', 'notripasal' => '', 'tgldoor' => '1900/1/1', 'statusdisc' => '0', 'statusformat' => '64', 'modifiedby' => 'ADMIN',]);

        SuratPengantar::create([ 'nobukti' => 'TRP 0008/X/2022', 'jobtrucking' => '0005/X/22', 'tglbukti' => '2022/10/18', 'pelanggan_id' => '1', 'keterangan' => '-', 'nourutorder' => '1', 'upah_id' => '1', 'dari_id' => '1', 'sampai_id' => '2', 'container_id' => '1', 'nocont' => 'SPNU 365142', 'nocont2' => '', 'statuscontainer_id' => '1', 'trado_id' => '1', 'supir_id' => '1', 'nojob' => 'PRE1/II/KTR - MD/JKT/22', 'nojob2' => '', 'statuslongtrip' => '66', 'omset' => '1021000', 'discount' => '0', 'totalomset' => '1021000', 'gajisupir' => '134760', 'gajikenek' => '15000', 'agen_id' => '2', 'jenisorder_id' => '1', 'statusperalihan' => '68', 'tarif_id' => '1', 'nominalperalihan' => '0', 'persentaseperalihan' => '0', 'biayatambahan_id' => '0', 'nosp' => '2022008076', 'tglsp' => '2022/10/18', 'statusritasiomset' => '70', 'cabang_id' => '3', 'komisisupir' => '0', 'tolsupir' => '0', 'jarak' => '33', 'nosptagihlain' => '', 'nilaitagihlain' => '0', 'tujuantagih' => '', 'liter' => '0', 'nominalstafle' => '0', 'statusnotif' => '72', 'statusoneway' => '74', 'statusedittujuan' => '76', 'upahbongkardepo' => '0', 'upahmuatdepo' => '0', 'hargatol' => '0', 'qtyton' => '0', 'totalton' => '0', 'mandorsupir_id' => '1', 'mandortrado_id' => '2', 'statustrip' => '78', 'notripasal' => '', 'tgldoor' => '1900/1/1', 'statusdisc' => '0', 'statusformat' => '64', 'modifiedby' => 'ADMIN',]);

        SuratPengantar::create([ 'nobukti' => 'TRP 0009/X/2022', 'jobtrucking' => '0005/X/22', 'tglbukti' => '2022/10/18', 'pelanggan_id' => '1', 'keterangan' => '-', 'nourutorder' => '1', 'upah_id' => '1', 'dari_id' => '2', 'sampai_id' => '1', 'container_id' => '1', 'nocont' => 'SPNU 321479', 'nocont2' => '', 'statuscontainer_id' => '1', 'trado_id' => '2', 'supir_id' => '2', 'nojob' => 'PRE1/II/KTR - MD/JKT/22', 'nojob2' => '', 'statuslongtrip' => '66', 'omset' => '1021000', 'discount' => '0', 'totalomset' => '1021000', 'gajisupir' => '134760', 'gajikenek' => '15000', 'agen_id' => '2', 'jenisorder_id' => '2', 'statusperalihan' => '68', 'tarif_id' => '1', 'nominalperalihan' => '0', 'persentaseperalihan' => '0', 'biayatambahan_id' => '0', 'nosp' => '2022008076', 'tglsp' => '2022/10/18', 'statusritasiomset' => '70', 'cabang_id' => '3', 'komisisupir' => '0', 'tolsupir' => '0', 'jarak' => '33', 'nosptagihlain' => '', 'nilaitagihlain' => '0', 'tujuantagih' => '', 'liter' => '0', 'nominalstafle' => '0', 'statusnotif' => '72', 'statusoneway' => '74', 'statusedittujuan' => '76', 'upahbongkardepo' => '0', 'upahmuatdepo' => '0', 'hargatol' => '0', 'qtyton' => '0', 'totalton' => '0', 'mandorsupir_id' => '1', 'mandortrado_id' => '2', 'statustrip' => '78', 'notripasal' => '', 'tgldoor' => '1900/1/1', 'statusdisc' => '0', 'statusformat' => '64', 'modifiedby' => 'ADMIN',]);


        SuratPengantar::create([ 'nobukti' => 'TRP 0010/X/2022', 'jobtrucking' => '0006/X/22', 'tglbukti' => '2022/10/18', 'pelanggan_id' => '1', 'keterangan' => '-', 'nourutorder' => '1', 'upah_id' => '1', 'dari_id' => '1', 'sampai_id' => '2', 'container_id' => '1', 'nocont' => 'SPNU 784123', 'nocont2' => '', 'statuscontainer_id' => '1', 'trado_id' => '2', 'supir_id' => '2', 'nojob' => 'PRE1/II/KTR - MD/JKT/22', 'nojob2' => '', 'statuslongtrip' => '66', 'omset' => '1240000', 'discount' => '0', 'totalomset' => '1240000', 'gajisupir' => '134760', 'gajikenek' => '15000', 'agen_id' => '1', 'jenisorder_id' => '1', 'statusperalihan' => '68', 'tarif_id' => '1', 'nominalperalihan' => '0', 'persentaseperalihan' => '0', 'biayatambahan_id' => '0', 'nosp' => '2022008076', 'tglsp' => '2022/10/18', 'statusritasiomset' => '70', 'cabang_id' => '3', 'komisisupir' => '0', 'tolsupir' => '0', 'jarak' => '33', 'nosptagihlain' => '', 'nilaitagihlain' => '0', 'tujuantagih' => '', 'liter' => '0', 'nominalstafle' => '0', 'statusnotif' => '72', 'statusoneway' => '74', 'statusedittujuan' => '76', 'upahbongkardepo' => '0', 'upahmuatdepo' => '0', 'hargatol' => '0', 'qtyton' => '0', 'totalton' => '0', 'mandorsupir_id' => '1', 'mandortrado_id' => '2', 'statustrip' => '78', 'notripasal' => '', 'tgldoor' => '1900/1/1', 'statusdisc' => '0', 'statusformat' => '64', 'modifiedby' => 'ADMIN',]);
        
    }
}
