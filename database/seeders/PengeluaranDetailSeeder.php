<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PengeluaranDetail;

class PengeluaranDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PengeluaranDetail::create([
            'pengeluaran_id' => '',
            'nobukti' => '',
            'alatbayar_id' => '',
            'nowarkat' => '',
            'tgljatuhtempo' => '',
            'nominal' => '',
            'coadebet' => '',
            'coakredit' => '',
            'keterangan' => '',
            'bank_id' => '',
            'noinvoice' => '',
            'statusedit' => '',
            'bulanbeban' => '',
            'modifiedby' => 'ADMIN',
        ]);
    }
}
