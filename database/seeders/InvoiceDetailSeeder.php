<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InvoiceDetail;

class InvoiceDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        InvoiceDetail::create([
            'nobukti' => 'INV 0001/II/2022',
            'invoice_id' => 1,
            'nominal' => 1021000,
            'keterangan' => 'INVOICE TRUCKING',
            'suratpengantar_nobukti' => 'TRP 0001/III/2022',
            'orderantrucking_nobukti' => 'TRP 0001/III/2022',
            'modifiedby' => 'ADMIN',
        ]);
    }
}
