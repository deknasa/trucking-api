<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PendapatanSupirDetail;

class PendapatanSupirDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PendapatanSupirDetail::create([
            'nobukti' => 'PST 0001/III/2022',
            'pendapatansupir_id' => 1,
            'supir_id' => 1,
            'nominal' => 80000,
            'keterangan' => 'PENDAPATAN SUPIR',
            'modifiedby' => 'ADMIN',
        ]);
    }
}
