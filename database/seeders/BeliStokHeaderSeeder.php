<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\BeliStokHeader;
class BeliStokHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        BeliStokHeader::create([
            'nobukti' => '',
            'tgl' => '',
            'postok_nobukti' => '',
            'supplier_id' => '',            
            'persentasedisc' => '',
            'nominaldisc' => '',
            'persentaseppn' => '',
            'nominalppn' => '',
            'total' => '',
            'keterangan' => '',
            'tgljatuhtempo' => '',
            'nobon' => '',
            'hutang_nobukti' => '',
            'modifiedby' => 'ADMIN',
        ]);
    }
}
