<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PengeluaranTruckingDetail;

class PengeluaranTruckingDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PengeluaranTruckingDetail::create([
            'pengeluarantruckingheader_id' => 1,
            'nobukti' => 'PJT 0001/III/2022',
            'supir_id' => 1,
            'penerimaantruckingheader_nobukti' =>'',
            'nominal' => 10000,
            'modifiedby' => 'ADMIN',
        ]);

        PengeluaranTruckingDetail::create([
            'pengeluarantruckingheader_id' => 2,
            'nobukti' => 'BSL 0001/III/2022',
            'supir_id' => 1,
            'penerimaantruckingheader_nobukti' =>'',
            'nominal' => 100000,
            'modifiedby' => 'ADMIN',
        ]);

        PengeluaranTruckingDetail::create([
            'pengeluarantruckingheader_id' => 1,
            'nobukti' => 'PJT 0001/IV/2022',
            'supir_id' => 1,
            'penerimaantruckingheader_nobukti' =>'',
            'nominal' => 15000,
            'modifiedby' => 'ADMIN',
        ]);

    }
}
