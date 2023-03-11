<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PenerimaanTrucking;
use Illuminate\Support\Facades\DB;

class PenerimaanTruckingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
<<<<<<< Updated upstream
        // DB::statement("delete PenerimaanTrucking");
        // DB::statement("DBCC CHECKIDENT ('PenerimaanTrucking', RESEED, 1);");

        // PenerimaanTrucking::create(['kodekota' => 'BLW', 'keterangan' => 'BELAWAN', 'zona_id' => '1', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
=======
        PenerimaanTrucking::create([
            'kodepenerimaan' => 'DPO',
            'keterangan' => 'DEPOSITO SUPIR',
            'coadebet'=> '01.04.02.01',
            'coakredit'=> '01.04.02.01',
            'coapostingdebet'=> '01.04.02.01',
            'coapostingkredit'=> '01.04.02.01',
            'format' => '126',
            'modifiedby' => 'ADMIN',
        ]);
     
        PenerimaanTrucking::create([
            'kodepenerimaan' => 'PJP',
            'keterangan' => 'PENGEMBALIAN PINJAMAN SUPIR',
            'coadebet'=> '01.05.02.02',
            'coakredit'=> '01.05.02.02',
            'coapostingdebet'=> '01.05.02.02',
            'coapostingkredit'=> '01.05.02.02',
            'format' => '125',
            'modifiedby' => 'ADMIN',
        ]);
>>>>>>> Stashed changes


   
        //
    }
}
