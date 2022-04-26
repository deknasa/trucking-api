<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InvoiceExtraHeader;

class InvoiceExtraHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        InvoiceExtraHeader::create([
            'nobukti' => 'INE 0001/II/2022',
            'tglbukti' => '2022/4/8',
            'pelanggan_id' => 1,
            'agen_id' => 2,
            'nominal' => 300000,
            'keterangan' => 'BIAYA CHARGE GANDENGAN',
            'modifiedby' => 'ADMIN',
        ]);
    }
}
