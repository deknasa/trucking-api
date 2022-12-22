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
    
        SuratPengantar::create([ 'nobukti' => 'TRP 0001/XII/2022', 'jobtrucking' => '0002/XII/2022', 'tglbukti' => '2022/12/22', 'pelanggan_id' => '1', 'keterangan' => '-', 'nourutorder' => '1', 'upah_id' => '1', 'dari_id' => '1', 'sampai_id' => '2', 'container_id' => '1', 'nocont' => 'CONT 24912', 'nocont2' => '', 'noseal' => 'SEAL 40129', 'noseal2' => '', 'statuscontainer_id' => '1', 'trado_id' => '1', 'gandengan_id' => '1', 'supir_id' => '1', 'nojob' => 'PRE1/II/KTR - MD/JKT/22', 'nojob2' => '', 'statuslongtrip' => '66', 'omset' => '1021000', 'discount' => '0', 'totalomset' => '1021000', 'gajisupir' => '500000', 'gajikenek' => '0', 'agen_id' => '1', 'jenisorder_id' => '1', 'statusperalihan' => '68', 'tarif_id' => '1', 'nominalperalihan' => '0', 'persentaseperalihan' => '0', 'biayatambahan_id' => '0', 'nosp' => '20221222001', 'tglsp' => '2022/12/22', 'statusritasiomset' => '203', 'cabang_id' => '3', 'komisisupir' => '0', 'tolsupir' => '0', 'jarak' => '12', 'nosptagihlain' => '', 'nilaitagihlain' => '0', 'tujuantagih' => '', 'liter' => '0', 'nominalstafle' => '0', 'statusnotif' => '72', 'statusoneway' => '74', 'statusedittujuan' => '76', 'upahbongkardepo' => '0', 'upahmuatdepo' => '0', 'hargatol' => '0', 'qtyton' => '4', 'totalton' => '0', 'mandorsupir_id' => '1', 'mandortrado_id' => '1', 'statustrip' => '78', 'notripasal' => '', 'tgldoor' => '1900/1/1', 'statusdisc' => '0', 'statusformat' => '64', 'statusgudangsama' => '205', 'gudang' => 'TES GUDANG', 'modifiedby' => 'ADMIN',]);

    }
}
