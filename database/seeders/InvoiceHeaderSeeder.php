<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InvoiceHeader;
use Illuminate\Support\Facades\DB;

class InvoiceHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete invoiceheader");
        DB::statement("DBCC CHECKIDENT ('invoiceheader', RESEED, 1);");

        invoiceheader::create(['nobukti' => 'INV 0001/II/2023', 'tglbukti' => '2023/2/10', 'keterangan' => '', 'nominal' => '48814750', 'tglterima' => '2023/2/10', 'tgljatuhtempo' => '2023/3/18', 'agen_id' => '64', 'jenisorder_id' => '2', 'cabang_id' => '0', 'piutang_nobukti' => 'EPT 0001/II/2023', 'statusapproval' => '4', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'tgldari' => '2023/2/1', 'tglsampai' => '2023/2/10', 'statusformat' => '151', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
    }
}
