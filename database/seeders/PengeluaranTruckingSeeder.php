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

        pengeluarantrucking::create(['kodepengeluaran' => 'PJT', 'keterangan' => 'PINJAMAN SUPIR', 'coadebet' => '01.05.03.02', 'coakredit' => '01.01.01.03', 'coapostingdebet' => '01.05.03.02', 'coapostingkredit' => '01.01.01.03', 'format' => '122', 'modifiedby' => 'ADMIN', 'jenisorder_id' => '0', 'info' => '',]);
        pengeluarantrucking::create(['kodepengeluaran' => 'TDE', 'keterangan' => 'PENARIKAN DEPOSITO', 'coadebet' => '01.04.03.01', 'coakredit' => '01.01.01.03', 'coapostingdebet' => '01.04.03.01', 'coapostingkredit' => '01.01.01.03', 'format' => '251', 'modifiedby' => 'ADMIN', 'jenisorder_id' => '0', 'info' => '',]);
        pengeluarantrucking::create(['kodepengeluaran' => 'BST', 'keterangan' => 'SUMBANGAN SOSIAL', 'coadebet' => '07.02.01.33', 'coakredit' => '01.01.01.03', 'coapostingdebet' => '07.02.01.33', 'coapostingkredit' => '01.01.01.03', 'format' => '289', 'modifiedby' => 'ADMIN', 'jenisorder_id' => '0', 'info' => '',]);
        pengeluarantrucking::create(['kodepengeluaran' => 'BSB', 'keterangan' => 'INSENTIF SUPIR', 'coadebet' => '07.01.01.10', 'coakredit' => '01.01.01.03', 'coapostingdebet' => '07.01.01.10', 'coapostingkredit' => '01.01.01.03', 'format' => '297', 'modifiedby' => 'ADMIN', 'jenisorder_id' => '0', 'info' => '',]);
        pengeluarantrucking::create(['kodepengeluaran' => 'KBBM', 'keterangan' => 'PELUNASAN HUTANG BBM', 'coadebet' => '03.02.02.07', 'coakredit' => '01.01.01.03', 'coapostingdebet' => '01.05.03.02', 'coapostingkredit' => '01.01.01.03', 'format' => '298', 'modifiedby' => 'ADMIN', 'jenisorder_id' => '0', 'info' => '',]);
        pengeluarantrucking::create(['kodepengeluaran' => 'BLS', 'keterangan' => 'BIAYA LAIN SUPIR', 'coadebet' => '', 'coakredit' => '', 'coapostingdebet' => '', 'coapostingkredit' => '', 'format' => '279', 'modifiedby' => 'ADMIN', 'jenisorder_id' => '0', 'info' => '',]);
        pengeluarantrucking::create(['kodepengeluaran' => 'KLAIM', 'keterangan' => 'KLAIM SUPIR', 'coadebet' => '', 'coakredit' => '', 'coapostingdebet' => '', 'coapostingkredit' => '', 'format' => '318', 'modifiedby' => 'ADMIN', 'jenisorder_id' => '0', 'info' => '',]);
        pengeluarantrucking::create(['kodepengeluaran' => 'PJK', 'keterangan' => 'PINJAMAN KARYAWAN', 'coadebet' => '01.05.03.01', 'coakredit' => '01.01.01.03', 'coapostingdebet' => '01.05.03.01', 'coapostingkredit' => '01.01.01.03', 'format' => '369', 'modifiedby' => 'ADMIN', 'jenisorder_id' => '0', 'info' => '',]);
        pengeluarantrucking::create(['kodepengeluaran' => 'BBT', 'keterangan' => 'TITIPAN EMKL', 'coadebet' => '01.08.01.06', 'coakredit' => '01.01.01.03', 'coapostingdebet' => '01.08.01.06', 'coapostingkredit' => '01.01.01.03', 'format' => '411', 'modifiedby' => 'ADMIN', 'jenisorder_id' => '0', 'info' => '',]);
        pengeluarantrucking::create(['kodepengeluaran' => 'BLL', 'keterangan' => 'BIAYA LAPANGAN LEMBUR', 'coadebet' => '07.01.01.03', 'coakredit' => '01.01.01.03', 'coapostingdebet' => '07.01.01.03', 'coapostingkredit' => '01.01.01.03', 'format' => '441', 'modifiedby' => 'ADMIN', 'jenisorder_id' => '0', 'info' => '',]);
        pengeluarantrucking::create(['kodepengeluaran' => 'BLN', 'keterangan' => 'BIAYA LAPANGAN NGINAP', 'coadebet' => '07.01.01.24', 'coakredit' => '01.01.01.03', 'coapostingdebet' => '07.01.01.24', 'coapostingkredit' => '01.01.01.03', 'format' => '442', 'modifiedby' => 'ADMIN', 'jenisorder_id' => '0', 'info' => '',]);
        pengeluarantrucking::create(['kodepengeluaran' => 'BTU', 'keterangan' => 'BIAYA LAPANGAN TAMBAHAN UANG JALAN', 'coadebet' => '07.01.01.21', 'coakredit' => '01.01.01.03', 'coapostingdebet' => '07.01.01.21', 'coapostingkredit' => '01.01.01.03', 'format' => '443', 'modifiedby' => 'ADMIN', 'jenisorder_id' => '0', 'info' => '',]);
        pengeluarantrucking::create(['kodepengeluaran' => 'BPT', 'keterangan' => 'BIAYA PORTAL', 'coadebet' => '07.01.01.04', 'coakredit' => '01.01.01.03', 'coapostingdebet' => '07.01.01.04', 'coapostingkredit' => '01.01.01.03', 'format' => '444', 'modifiedby' => 'ADMIN', 'jenisorder_id' => '0', 'info' => '',]);
        pengeluarantrucking::create(['kodepengeluaran' => 'BGS', 'keterangan' => 'BIAYA GAJI SUPIR', 'coadebet' => '07.02.01.07', 'coakredit' => '01.01.01.03', 'coapostingdebet' => '07.02.01.07', 'coapostingkredit' => '01.01.01.03', 'format' => '445', 'modifiedby' => 'ADMIN', 'jenisorder_id' => '0', 'info' => '',]);
        pengeluarantrucking::create(['kodepengeluaran' => 'BIT', 'keterangan' => 'BIAYA INSENTIF', 'coadebet' => '07.01.01.10', 'coakredit' => '01.01.01.03', 'coapostingdebet' => '07.01.01.10', 'coapostingkredit' => '01.01.01.03', 'format' => '446', 'modifiedby' => 'ADMIN', 'jenisorder_id' => '0', 'info' => '',]);
    }
}
