<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NotaDebetDetail;

class NotaDebetDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        NotaDebetDetail::create([
            'nobukti' => 'NDT 0001/V/2022',
            'notadebet_id' => 1,
            'tglterima' => '2022/5/20',
            'invoice_nobukti' => '',
            'nominal' => 100000,
            'nominalbayar' => 100000,
            'lebihbayar' => 5000,
            'keterangan' => '',
            'coalebihbayar' => '06.02.01.01',
            'modifiedby' => 'ADMIN',
        ]);
    }
}
