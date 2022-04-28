<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PenerimaanTruckingDetail;

class PenerimaanTruckingDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PenerimaanTruckingDetail::create([
            'penerimaantruckingheader_id' => 1,
            'nobukti' => 'DPO 0001/III/2022',
            'supir_id' => 1,
            'pengeluarantruckingheader_nobukti' =>'',
            'nominal' => 10000,
            'modifiedby' => 'ADMIN',
        ]);

        PenerimaanTruckingDetail::create([
            'penerimaantruckingheader_id' => 2,
            'nobukti' => 'PJP 0001/III/2022',
            'supir_id' => 1,
            'pengeluarantruckingheader_nobukti' =>'PJT 0001/III/2022',
            'nominal' => 10000,
            'modifiedby' => 'ADMIN',
        ]);

        PenerimaanTruckingDetail::create([
            'penerimaantruckingheader_id' => 3,
            'nobukti' => 'DPO 0001/IV/2022',
            'supir_id' => 1,
            'pengeluarantruckingheader_nobukti' =>'',
            'nominal' => 10000,
            'modifiedby' => 'ADMIN',
        ]);

    }
}
