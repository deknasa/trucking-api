<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProsesGajiSupirHeader;
use Illuminate\Support\Facades\DB;

class ProsesGajiSupirHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete prosesgajisupirheader");
        DB::statement("DBCC CHECKIDENT ('prosesgajisupirheader', RESEED, 1);");

        prosesgajisupirheader::create([ 'nobukti' => 'EBS 0001/II/2023', 'tglbukti' => '2023/2/1', 'keterangan' => '', 'tgldari' => '2023/2/1', 'tglsampai' => '2023/2/1', 'pengeluaran_nobukti' => 'KBT 0005/II/2023', 'bank_id' => '1', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'periode' => '2023/2/1', 'statusformat' => '148', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);

    }
}
