<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pinjaman;

class PinjamanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Pinjaman::create([
            'nobukti' => 'PJT 0001/III/2022',
            'tgl' => '2022/3/21',
            'keterangan' => 'PINJAMAN SUPIR',
            'supir_id' => 1,
            'bank_id' => 1,
            'dp' => 0,
            'nominal' => 10000,
            'nominalcicilan' => 0,
            'coa' => '01.05.02.02',
            'nobuktikaskeluar' => 'KBT 0001/III/2022',
            'tglkaskeluar' => '2022/3/21',
            'statusposting' => 83,
            'modifiedby' => 'ADMIN',
        ]);
    }
}
