<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PenerimaanHeader;
use Illuminate\Support\Facades\DB;

class PenerimaanHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete penerimaanheader");
        DB::statement("DBCC CHECKIDENT ('penerimaanheader', RESEED, 1);");

        penerimaanheader::create(['nobukti' => 'BMT-M BCA3 0001/II/2023', 'tglbukti' => '2023/2/1', 'pelanggan_id' => '0', 'agen_id' => '0', 'bank_id' => '4', 'postingdari' => 'ENTRY PENERIMAAN KAS/BANK', 'diterimadari' => 'KANTOR PUSAT', 'tgllunas' => '2023/2/1', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'statusberkas' => '0', 'userberkas' => '', 'tglberkas' => '1900/1/1', 'statusformat' => '268', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
        penerimaanheader::create(['nobukti' => 'BMT-M BCA3 0002/II/2023', 'tglbukti' => '2023/2/1', 'pelanggan_id' => '0', 'agen_id' => '63', 'bank_id' => '4', 'postingdari' => 'ENTRY PELUNASAN PIUTANG', 'diterimadari' => 'TAS AHAI', 'tgllunas' => '2023/2/1', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'statusberkas' => '0', 'userberkas' => '', 'tglberkas' => '1900/1/1', 'statusformat' => '268', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
    }
}
