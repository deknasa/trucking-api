<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PengeluaranTrucking;
use Illuminate\Support\Facades\DB;

class PengeluaranTruckingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete PengeluaranTrucking");
        DB::statement("DBCC CHECKIDENT ('PengeluaranTrucking', RESEED, 1);");

        pengeluarantrucking::create(['kodepengeluaran' => 'PJT', 'keterangan' => 'PINJAMAN SUPIR', 'coadebet' => '01.05.02.02', 'coakredit' => '01.01.01.02', 'coapostingdebet' => '01.05.02.02', 'coapostingkredit' => '01.01.01.02', 'format' => '122', 'modifiedby' => 'ADMIN',]);
        pengeluarantrucking::create(['kodepengeluaran' => 'TDE', 'keterangan' => 'PENARIKAN DEPOSITO', 'coadebet' => '01.04.02.01', 'coakredit' => '01.01.01.02', 'coapostingdebet' => '01.04.02.01', 'coapostingkredit' => '01.01.01.02', 'format' => '251', 'modifiedby' => 'ADMIN',]);
        pengeluarantrucking::create(['kodepengeluaran' => 'BST', 'keterangan' => 'SUMBANGAN SOSIAL', 'coadebet' => '07.02.01.33', 'coakredit' => '01.01.01.02', 'coapostingdebet' => '07.02.01.33', 'coapostingkredit' => '01.01.01.02', 'format' => '251', 'modifiedby' => 'ADMIN',]);
        pengeluarantrucking::create(['kodepengeluaran' => 'BSB', 'keterangan' => 'INSENTIF SUPIR', 'coadebet' => '07.01.01.10', 'coakredit' => '01.01.01.02', 'coapostingdebet' => '07.01.01.10', 'coapostingkredit' => '01.01.01.02', 'format' => '251', 'modifiedby' => 'ADMIN',]);
        pengeluarantrucking::create(['kodepengeluaran' => 'KBBM', 'keterangan' => 'PELUNASAN HUTANG BBM', 'coadebet' => '03.02.02.07', 'coakredit' => '01.01.01.02', 'coapostingdebet' => '03.02.02.07', 'coapostingkredit' => '01.01.01.02', 'format' => '251', 'modifiedby' => 'ADMIN',]);
    }
}
