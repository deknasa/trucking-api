<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProsesGajiSupirDetail;
use Illuminate\Support\Facades\DB;

class ProsesGajiSupirDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete ProsesGajiSupirDetail");
        DB::statement("DBCC CHECKIDENT ('ProsesGajiSupirDetail', RESEED, 1);");

        prosesgajisupirdetail::create(['prosesgajisupir_id' => '1', 'nobukti' => 'EBS 0001/II/2023', 'gajisupir_nobukti' => 'RIC 0001/II/2023', 'supir_id' => '60', 'trado_id' => '27', 'nominal' => '317689', 'keterangan' => '', 'modifiedby' => 'ADMIN',]);
        prosesgajisupirdetail::create(['prosesgajisupir_id' => '1', 'nobukti' => 'EBS 0001/II/2023', 'gajisupir_nobukti' => 'RIC 0002/II/2023', 'supir_id' => '175', 'trado_id' => '24', 'nominal' => '745706', 'keterangan' => '', 'modifiedby' => 'ADMIN',]);
        prosesgajisupirdetail::create(['prosesgajisupir_id' => '1', 'nobukti' => 'EBS 0001/II/2023', 'gajisupir_nobukti' => 'RIC 0003/II/2023', 'supir_id' => '83', 'trado_id' => '14', 'nominal' => '319248', 'keterangan' => '', 'modifiedby' => 'ADMIN',]);
        prosesgajisupirdetail::create(['prosesgajisupir_id' => '1', 'nobukti' => 'EBS 0001/II/2023', 'gajisupir_nobukti' => 'RIC 0004/II/2023', 'supir_id' => '83', 'trado_id' => '14', 'nominal' => '75000', 'keterangan' => '', 'modifiedby' => 'ADMIN',]);
        prosesgajisupirdetail::create(['prosesgajisupir_id' => '1', 'nobukti' => 'EBS 0001/II/2023', 'gajisupir_nobukti' => 'RIC 0005/II/2023', 'supir_id' => '94', 'trado_id' => '7', 'nominal' => '788216', 'keterangan' => '', 'modifiedby' => 'ADMIN',]);
        prosesgajisupirdetail::create(['prosesgajisupir_id' => '1', 'nobukti' => 'EBS 0001/II/2023', 'gajisupir_nobukti' => 'RIC 0006/II/2023', 'supir_id' => '72', 'trado_id' => '30', 'nominal' => '771432', 'keterangan' => '', 'modifiedby' => 'ADMIN',]);
        prosesgajisupirdetail::create(['prosesgajisupir_id' => '1', 'nobukti' => 'EBS 0001/II/2023', 'gajisupir_nobukti' => 'RIC 0007/II/2023', 'supir_id' => '73', 'trado_id' => '38', 'nominal' => '575020', 'keterangan' => '', 'modifiedby' => 'ADMIN',]);
        prosesgajisupirdetail::create(['prosesgajisupir_id' => '1', 'nobukti' => 'EBS 0001/II/2023', 'gajisupir_nobukti' => 'RIC 0008/II/2023', 'supir_id' => '267', 'trado_id' => '39', 'nominal' => '561612', 'keterangan' => '', 'modifiedby' => 'ADMIN',]);
        prosesgajisupirdetail::create(['prosesgajisupir_id' => '1', 'nobukti' => 'EBS 0001/II/2023', 'gajisupir_nobukti' => 'RIC 0009/II/2023', 'supir_id' => '76', 'trado_id' => '14', 'nominal' => '1309464', 'keterangan' => '', 'modifiedby' => 'ADMIN',]);
        prosesgajisupirdetail::create(['prosesgajisupir_id' => '1', 'nobukti' => 'EBS 0001/II/2023', 'gajisupir_nobukti' => 'RIC 0010/II/2023', 'supir_id' => '172', 'trado_id' => '7', 'nominal' => '839420', 'keterangan' => '', 'modifiedby' => 'ADMIN',]);
        prosesgajisupirdetail::create(['prosesgajisupir_id' => '1', 'nobukti' => 'EBS 0001/II/2023', 'gajisupir_nobukti' => 'RIC 0011/II/2023', 'supir_id' => '171', 'trado_id' => '15', 'nominal' => '1009536', 'keterangan' => '', 'modifiedby' => 'ADMIN',]);
        prosesgajisupirdetail::create(['prosesgajisupir_id' => '1', 'nobukti' => 'EBS 0001/II/2023', 'gajisupir_nobukti' => 'RIC 0012/II/2023', 'supir_id' => '298', 'trado_id' => '8', 'nominal' => '714664', 'keterangan' => '', 'modifiedby' => 'ADMIN',]);
        prosesgajisupirdetail::create(['prosesgajisupir_id' => '1', 'nobukti' => 'EBS 0001/II/2023', 'gajisupir_nobukti' => 'RIC 0013/II/2023', 'supir_id' => '305', 'trado_id' => '11', 'nominal' => '1130900', 'keterangan' => '', 'modifiedby' => 'ADMIN',]);
        prosesgajisupirdetail::create(['prosesgajisupir_id' => '1', 'nobukti' => 'EBS 0001/II/2023', 'gajisupir_nobukti' => 'RIC 0014/II/2023', 'supir_id' => '307', 'trado_id' => '12', 'nominal' => '552925', 'keterangan' => '', 'modifiedby' => 'ADMIN',]);
        prosesgajisupirdetail::create(['prosesgajisupir_id' => '1', 'nobukti' => 'EBS 0001/II/2023', 'gajisupir_nobukti' => 'RIC 0015/II/2023', 'supir_id' => '73', 'trado_id' => '38', 'nominal' => '158680', 'keterangan' => '', 'modifiedby' => 'ADMIN',]);
        prosesgajisupirdetail::create(['prosesgajisupir_id' => '1', 'nobukti' => 'EBS 0001/II/2023', 'gajisupir_nobukti' => 'RIC 0016/II/2023', 'supir_id' => '146', 'trado_id' => '6', 'nominal' => '1089683', 'keterangan' => '', 'modifiedby' => 'ADMIN',]);
        prosesgajisupirdetail::create(['prosesgajisupir_id' => '1', 'nobukti' => 'EBS 0001/II/2023', 'gajisupir_nobukti' => 'RIC 0017/II/2023', 'supir_id' => '311', 'trado_id' => '25', 'nominal' => '2310788', 'keterangan' => '', 'modifiedby' => 'ADMIN',]);
        prosesgajisupirdetail::create(['prosesgajisupir_id' => '1', 'nobukti' => 'EBS 0001/II/2023', 'gajisupir_nobukti' => 'RIC 0018/II/2023', 'supir_id' => '72', 'trado_id' => '30', 'nominal' => '95333', 'keterangan' => '', 'modifiedby' => 'ADMIN',]);
    }
}
