<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InvoiceHeader;

class InvoiceHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        InvoiceHeader::create([
            'nobukti' => 'INV 0001/II/2022',
            'tglbukti' => '2022/4/8',
            'nominal' => 1021000,
            'keterangan' => 'INVOICE', 
            'tglterima' => '2022/4/8',
            'tgljatuhtempo' => '2022/4/8',
            'agen_id' => 2,
            'jenisorder_id' => 1,
            'cabang_id' => 3,
            'piutang_nobukti' => '', 
            'statusapproval' => 4,
            'userapproval' => '', 
            'tglapproval' => '',
            'modifiedby' => 'ADMIN',
        ]);

    }
}
