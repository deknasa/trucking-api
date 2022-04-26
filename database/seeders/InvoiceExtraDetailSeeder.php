<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InvoiceExtraDetail;

class InvoiceExtraDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        InvoiceExtraDetail::create([
            'nobukti' => 'INE 0001/II/2022',
            'invoiceextra_id' => 1,
            'nominal' => 300000,
            'keterangan' => 'BIAYA CHARGE GANDENGAN',
            'modifiedby' => 'ADMIN'
        ]);
    }
}
