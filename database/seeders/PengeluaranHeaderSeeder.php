<?php

namespace Database\Seeders;

use App\Models\PengeluaranHeader;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PengeluaranHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete pengeluaranheader");
        DB::statement("DBCC CHECKIDENT ('pengeluaranheader', RESEED, 1);");

        pengeluaranheader::create(['nobukti' => 'KBT 0001/II/2023', 'tglbukti' => '2023/2/1', 'pelanggan_id' => '0', 'alatbayar_id' => '0', 'postingdari' => 'ENTRY PENGELUARAN TRUCKING', 'statusapproval' => '4', 'dibayarke' => '', 'bank_id' => '1', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'transferkeac' => '', 'transferkean' => '', 'transferkebank' => '', 'statusformat' => '33', 'modifiedby' => 'ADMIN',]);
        pengeluaranheader::create(['nobukti' => 'KBT 0002/II/2023', 'tglbukti' => '2023/2/1', 'pelanggan_id' => '0', 'alatbayar_id' => '0', 'postingdari' => 'ENTRY PENGELUARAN TRUCKING', 'statusapproval' => '4', 'dibayarke' => '', 'bank_id' => '1', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'transferkeac' => '', 'transferkean' => '', 'transferkebank' => '', 'statusformat' => '33', 'modifiedby' => 'ADMIN',]);
        pengeluaranheader::create(['nobukti' => 'KBT 0003/II/2023', 'tglbukti' => '2023/2/1', 'pelanggan_id' => '0', 'alatbayar_id' => '0', 'postingdari' => 'ENTRY PENGELUARAN TRUCKING', 'statusapproval' => '4', 'dibayarke' => '', 'bank_id' => '1', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'transferkeac' => '', 'transferkean' => '', 'transferkebank' => '', 'statusformat' => '33', 'modifiedby' => 'ADMIN',]);
        pengeluaranheader::create(['nobukti' => 'KBT 0004/II/2023', 'tglbukti' => '2023/2/1', 'pelanggan_id' => '0', 'alatbayar_id' => '0', 'postingdari' => 'ENTRY PENGELUARAN TRUCKING', 'statusapproval' => '4', 'dibayarke' => '', 'bank_id' => '1', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'transferkeac' => '', 'transferkean' => '', 'transferkebank' => '', 'statusformat' => '33', 'modifiedby' => 'ADMIN',]);
    }
}
