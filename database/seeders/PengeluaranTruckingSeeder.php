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
        DB::statement("DBCC CHECKIDENT ('PengeluaranTrucking', RESEED, 0);");

        pengeluarantrucking::create(['kodepengeluaran' => 'PJT', 'keterangan' => 'PINJAMAN SUPIR', 'coadebet' => '01.05.02.02', 'coakredit' => '01.01.01.02', 'coapostingdebet' => '01.05.02.02', 'coapostingkredit' => '01.01.01.02', 'format' => '122', 'modifiedby' => 'ADMIN',]);
        pengeluarantrucking::create(['kodepengeluaran' => 'TDE', 'keterangan' => 'PENARIKAN DEPOSITO', 'coadebet' => '01.04.02.01', 'coakredit' => '01.01.01.02', 'coapostingdebet' => '01.04.02.01', 'coapostingkredit' => '01.01.01.02', 'format' => '251', 'modifiedby' => 'ADMIN',]);
        pengeluarantrucking::create(['kodepengeluaran' => 'BST', 'keterangan' => 'SUMBANGAN SOSIAL', 'coadebet' => '07.02.01.33', 'coakredit' => '01.01.01.02', 'coapostingdebet' => '07.02.01.33', 'coapostingkredit' => '01.01.01.02', 'format' => '289', 'modifiedby' => 'ADMIN',]);
        pengeluarantrucking::create(['kodepengeluaran' => 'BSB', 'keterangan' => 'INSENTIF SUPIR', 'coadebet' => '07.01.01.10', 'coakredit' => '01.01.01.02', 'coapostingdebet' => '07.01.01.10', 'coapostingkredit' => '01.01.01.02', 'format' => '297', 'modifiedby' => 'ADMIN',]);
        pengeluarantrucking::create(['kodepengeluaran' => 'KBBM', 'keterangan' => 'PELUNASAN HUTANG BBM', 'coadebet' => '03.02.02.07', 'coakredit' => '01.01.01.02', 'coapostingdebet' => '03.02.02.07', 'coapostingkredit' => '01.01.01.02', 'format' => '298', 'modifiedby' => 'ADMIN',]);
        pengeluarantrucking::create(['kodepengeluaran' => 'BLS', 'keterangan' => 'BIAYA LAIN SUPIR', 'coadebet' => '', 'coakredit' => '', 'coapostingdebet' => '', 'coapostingkredit' => '', 'format' => '279', 'modifiedby' => 'ADMIN',]);
        pengeluarantrucking::create(['kodepengeluaran' => 'KLAIM', 'keterangan' => 'KLAIM SUPIR', 'coadebet' => '', 'coakredit' => '', 'coapostingdebet' => '', 'coapostingkredit' => '', 'format' => '318', 'modifiedby' => 'ADMIN',]);
        pengeluarantrucking::create(['kodepengeluaran' => 'PJK', 'keterangan' => 'PINJAMAN KARYAWAN', 'coadebet' => '01.05.02.02', 'coakredit' => '01.01.01.02', 'coapostingdebet' => '01.05.02.02', 'coapostingkredit' => '01.01.01.02', 'format' => '369', 'modifiedby' => 'ADMIN',]);
        pengeluarantrucking::create(['kodepengeluaran' => 'BBT', 'keterangan' => 'TITIPAN EMKL', 'coadebet' => '01.08.01.06', 'coakredit' => '01.01.01.02', 'coapostingdebet' => '01.08.01.06', 'coapostingkredit' => '01.01.01.02', 'format' => '411', 'modifiedby' => 'ADMIN',]);
        pengeluarantrucking::create(['kodepengeluaran' => 'BBT', 'keterangan' => 'BIAYA LAPANGAN LEMBUR', 'coadebet' => '01.08.01.06', 'coakredit' => '01.01.01.02', 'coapostingdebet' => '01.08.01.06', 'coapostingkredit' => '01.01.01.02', 'format' => '441', 'modifiedby' => 'ADMIN',]);
        pengeluarantrucking::create(['kodepengeluaran' => 'BLN', 'keterangan' => 'BIAYA LAPANGAN NGINAP', 'coadebet' => '01.08.01.06', 'coakredit' => '01.01.01.02', 'coapostingdebet' => '01.08.01.06', 'coapostingkredit' => '01.01.01.02', 'format' => '442', 'modifiedby' => 'ADMIN',]);
        pengeluarantrucking::create(['kodepengeluaran' => 'BTU', 'keterangan' => 'BIAYA LAPANGAN TAMBAHAN UANG JALAN', 'coadebet' => '01.08.01.06', 'coakredit' => '01.01.01.02', 'coapostingdebet' => '01.08.01.06', 'coapostingkredit' => '01.01.01.02', 'format' => '443', 'modifiedby' => 'ADMIN',]);
        pengeluarantrucking::create(['kodepengeluaran' => 'BPT', 'keterangan' => 'BIAYA PORTAL', 'coadebet' => '01.08.01.06', 'coakredit' => '01.01.01.02', 'coapostingdebet' => '01.08.01.06', 'coapostingkredit' => '01.01.01.02', 'format' => '444', 'modifiedby' => 'ADMIN',]);
        pengeluarantrucking::create(['kodepengeluaran' => 'BGS', 'keterangan' => 'BIAYA GAJI SUPIR', 'coadebet' => '01.08.01.06', 'coakredit' => '01.01.01.02', 'coapostingdebet' => '01.08.01.06', 'coapostingkredit' => '01.01.01.02', 'format' => '445', 'modifiedby' => 'ADMIN',]);
        pengeluarantrucking::create(['kodepengeluaran' => 'BIT', 'keterangan' => 'BIAYA INSENTIF', 'coadebet' => '01.08.01.06', 'coakredit' => '01.01.01.02', 'coapostingdebet' => '01.08.01.06', 'coapostingkredit' => '01.01.01.02', 'format' => '446', 'modifiedby' => 'ADMIN',]);


    }
}
