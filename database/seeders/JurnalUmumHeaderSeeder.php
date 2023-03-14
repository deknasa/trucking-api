<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JurnalUmumHeader;
use Illuminate\Support\Facades\DB;

class JurnalUmumHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete jurnalumumheader");
        DB::statement("DBCC CHECKIDENT ('jurnalumumheader', RESEED, 1);");

        jurnalumumheader::create(['nobukti' => 'BMT-M BCA3 0001/II/2023', 'tglbukti' => '2023/2/1', 'postingdari' => 'ENTRY PENERIMAAN KAS/BANK', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'statusformat' => '0', 'modifiedby' => 'ADMIN',]);
        jurnalumumheader::create(['nobukti' => 'KBT 0001/II/2023', 'tglbukti' => '2023/2/1', 'postingdari' => 'ENTRY PENGELUARAN TRUCKING', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'statusformat' => '0', 'modifiedby' => 'ADMIN',]);
        jurnalumumheader::create(['nobukti' => 'KBT 0002/II/2023', 'tglbukti' => '2023/2/1', 'postingdari' => 'ENTRY PENGELUARAN TRUCKING', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'statusformat' => '0', 'modifiedby' => 'ADMIN',]);
        jurnalumumheader::create(['nobukti' => 'KBT 0003/II/2023', 'tglbukti' => '2023/2/1', 'postingdari' => 'ENTRY PENGELUARAN TRUCKING', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'statusformat' => '0', 'modifiedby' => 'ADMIN',]);
        jurnalumumheader::create(['nobukti' => 'KBT 0004/II/2023', 'tglbukti' => '2023/2/1', 'postingdari' => 'ENTRY PENGELUARAN TRUCKING', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'statusformat' => '0', 'modifiedby' => 'ADMIN',]);
    }
}
