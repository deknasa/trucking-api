<?php

namespace Database\Seeders;

use App\Models\HutangHeader;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HutangHeaderSeeder extends Seeder
{
    /**git 
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete HutangHeader");
        DB::statement("DBCC CHECKIDENT ('HutangHeader', RESEED, 1);");

        hutangheader::create(['nobukti' => 'EHT 0001/II/2023', 'tglbukti' => '2023/2/1', 'keterangan' => '', 'coa' => '01.10.02.01', 'coakredit' => '', 'total' => '181000', 'postingdari' => 'PENERIMAAN STOK PEMBELIAN', 'pelanggan_id' => '0', 'supplier_id' => '24', 'statusformat' => '127', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
        hutangheader::create(['nobukti' => 'EHT 0002/II/2023', 'tglbukti' => '2023/2/3', 'keterangan' => '', 'coa' => '01.10.02.01', 'coakredit' => '', 'total' => '351000', 'postingdari' => 'PENERIMAAN STOK PEMBELIAN', 'pelanggan_id' => '0', 'supplier_id' => '25', 'statusformat' => '127', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
    }
}
