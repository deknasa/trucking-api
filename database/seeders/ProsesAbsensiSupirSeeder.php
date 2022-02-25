<?php

namespace Database\Seeders;

use App\Models\ProsesAbsensiSupir;
use Illuminate\Database\Seeder;

class ProsesAbsensiSupirSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ProsesAbsensiSupir::create([
            'nobukti' => 'PAB 0001/II/2022',
            'tgl' => '2022-02-24',
            'keterangan' => 'PROSES ABSENSI SUPIR',
            'pengeluaran_nobukti' => 'KBT 0001/II/2022',
            'absensisupir_nobukti' => 'ABS 0001/II/2022',
            'nominal' =>250000 ,
            'modifiedby' => 'ADMIN',
            ]);
    }
    
}
