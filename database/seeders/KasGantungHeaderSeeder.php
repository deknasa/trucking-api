<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\KasGantungHeader;
use Illuminate\Support\Facades\DB;

class KasGantungHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete KasGantungHeader");
        DB::statement("DBCC CHECKIDENT ('KasGantungHeader', RESEED, 1);");

        kasgantungheader::create(['nobukti' => 'KGT 0001/II/2023', 'tglbukti' => '2023/2/1', 'keterangan' => '', 'penerima_id' => '0', 'bank_id' => '1', 'pengeluaran_nobukti' => '', 'coakaskeluar' => '', 'postingdari' => 'ENTRY ABSENSI SUPIR', 'tglkaskeluar' => '2023/2/1', 'statusformat' => '52', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
        kasgantungheader::create(['nobukti' => 'KGT 0002/II/2023', 'tglbukti' => '2023/2/2', 'keterangan' => '', 'penerima_id' => '0', 'bank_id' => '1', 'pengeluaran_nobukti' => '', 'coakaskeluar' => '', 'postingdari' => 'ENTRY ABSENSI SUPIR', 'tglkaskeluar' => '2023/2/2', 'statusformat' => '52', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
    }
}
