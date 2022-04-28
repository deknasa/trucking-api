<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PendapatanSupirHeader;

class PendapatanSupirHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PendapatanSupirHeader::create([
            'nobukti' => 'PST 0001/III/2022',
            'tglbukti' => '2022/4/8',
            'bank_id' => 1,
            'keterangan' => 'PENDAPATAN SUPIR TRUCKING',
            'tgldari' => '2022/3/1',
            'tglsampai' => '2022/3/30',
            'statusapproval' => 4,
            'userapproval' => '',
            'tglapproval' => '',
            'periode' => '2022/3/1',
            'modifiedby' => 'ADMIN',
        ]);
    }
}
