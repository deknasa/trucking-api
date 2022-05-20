<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NotaKreditDetail;

class NotaKreditDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        NotaKreditDetail::create([
            'notakredit_id' => 1,
            'nobukti' => 'NKT 0001/V/2022',
            'tglterima' => '2022/5/22',
            'invoice_nobukti' => 'INE 0001/V/2022',
            'nominal' => 0,
            'nominalbayar' => 0,
            'penyesuaian' => 300000,
            'keterangan' => 'POTONGAN',
            'coaadjust' => '06.03.01.01',
            'modifiedby' => 'ADMIN',
        ]);
    }
}
