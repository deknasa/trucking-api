<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PelunasanPiutangHeader;
use Illuminate\Support\Facades\DB;

class PelunasanPiutangHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete pelunasanpiutangheader");
        DB::statement("DBCC CHECKIDENT ('pelunasanpiutangheader', RESEED, 1);");

        pelunasanpiutangheader::create(['nobukti' => 'PPT 0001/II/2023', 'tglbukti' => '2023/2/1', 'keterangan' => '', 'bank_id' => '4', 'agen_id' => '63', 'cabang_id' => '0', 'alatbayar_id' => '2', 'pelanggan_id' => '0', 'penerimaangiro_nobukti' => '-', 'penerimaan_nobukti' => 'BMT-M BCA3 0002/II/2023', 'notakredit_nobukti' => '-', 'notadebet_nobukti' => '-', 'tglcair' => '1900/1/1', 'nowarkat' => '-', 'statusformat' => '128', 'modifiedby' => 'ADMIN',]);
        pelunasanpiutangheader::create(['nobukti' => 'PPT 0002/II/2023', 'tglbukti' => '2023/2/1', 'keterangan' => '', 'bank_id' => '4', 'agen_id' => '64', 'cabang_id' => '0', 'alatbayar_id' => '2', 'pelanggan_id' => '0', 'penerimaangiro_nobukti' => '-', 'penerimaan_nobukti' => 'BMT-M BCA3 0003/II/2023', 'notakredit_nobukti' => '-', 'notadebet_nobukti' => '-', 'tglcair' => '1900/1/1', 'nowarkat' => '-', 'statusformat' => '128', 'modifiedby' => 'ADMIN',]);
        pelunasanpiutangheader::create(['nobukti' => 'PPT 0003/II/2023', 'tglbukti' => '2023/2/9', 'keterangan' => '', 'bank_id' => '4', 'agen_id' => '64', 'cabang_id' => '0', 'alatbayar_id' => '2', 'pelanggan_id' => '0', 'penerimaangiro_nobukti' => '-', 'penerimaan_nobukti' => 'BMT-M BCA3 0010/II/2023', 'notakredit_nobukti' => '-', 'notadebet_nobukti' => '-', 'tglcair' => '1900/1/1', 'nowarkat' => '-', 'statusformat' => '128', 'modifiedby' => 'ADMIN',]);
        pelunasanpiutangheader::create(['nobukti' => 'PPT 0004/II/2023', 'tglbukti' => '2023/2/16', 'keterangan' => '', 'bank_id' => '4', 'agen_id' => '64', 'cabang_id' => '0', 'alatbayar_id' => '2', 'pelanggan_id' => '0', 'penerimaangiro_nobukti' => '-', 'penerimaan_nobukti' => 'BMT-M BCA3 0013/II/2023', 'notakredit_nobukti' => '-', 'notadebet_nobukti' => '-', 'tglcair' => '1900/1/1', 'nowarkat' => '-', 'statusformat' => '128', 'modifiedby' => 'ADMIN',]);
        pelunasanpiutangheader::create(['nobukti' => 'PPT 0005/II/2023', 'tglbukti' => '2023/2/23', 'keterangan' => '', 'bank_id' => '4', 'agen_id' => '64', 'cabang_id' => '0', 'alatbayar_id' => '2', 'pelanggan_id' => '0', 'penerimaangiro_nobukti' => '-', 'penerimaan_nobukti' => 'BMT-M BCA3 0016/II/2023', 'notakredit_nobukti' => '-', 'notadebet_nobukti' => '-', 'tglcair' => '1900/1/1', 'nowarkat' => '-', 'statusformat' => '128', 'modifiedby' => 'ADMIN',]);
    }
}
