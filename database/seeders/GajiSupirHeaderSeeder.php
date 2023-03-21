<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GajiSupirHeader;
use Illuminate\Support\Facades\DB;

class GajiSupirHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete GajiSupirHeader");
        DB::statement("DBCC CHECKIDENT ('GajiSupirHeader', RESEED, 1);");

        gajisupirheader::create(['nobukti' => 'RIC 0001/II/2023', 'tglbukti' => '2023/2/1', 'keterangan' => '', 'supir_id' => '60', 'nominal' => '-12845', 'tgldari' => '2023/1/27', 'tglsampai' => '2023/1/28', 'total' => '317689', 'uangjalan' => '200000', 'bbm' => '280534', 'potonganpinjaman' => '0', 'deposito' => '0', 'potonganpinjamansemua' => '0', 'komisisupir' => '10000', 'tolsupir' => '0', 'voucher' => '0', 'uangmakanharian' => '150000', 'pinjamanpribadi' => '0', 'gajiminus' => '0', 'uangJalantidakterhitung' => '0', 'statusformat' => '146', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
        gajisupirheader::create(['nobukti' => 'RIC 0002/II/2023', 'tglbukti' => '2023/2/1', 'keterangan' => '', 'supir_id' => '175', 'nominal' => '251894', 'tgldari' => '2023/1/27', 'tglsampai' => '2023/1/28', 'total' => '745706', 'uangjalan' => '200000', 'bbm' => '418812', 'potonganpinjaman' => '0', 'deposito' => '25000', 'potonganpinjamansemua' => '0', 'komisisupir' => '10000', 'tolsupir' => '0', 'voucher' => '0', 'uangmakanharian' => '150000', 'pinjamanpribadi' => '0', 'gajiminus' => '0', 'uangJalantidakterhitung' => '0', 'statusformat' => '146', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
        gajisupirheader::create(['nobukti' => 'RIC 0003/II/2023', 'tglbukti' => '2023/2/1', 'keterangan' => '', 'supir_id' => '83', 'nominal' => '-100780', 'tgldari' => '2023/1/27', 'tglsampai' => '2023/1/30', 'total' => '319248', 'uangjalan' => '300000', 'bbm' => '270028', 'potonganpinjaman' => '0', 'deposito' => '0', 'potonganpinjamansemua' => '0', 'komisisupir' => '10000', 'tolsupir' => '0', 'voucher' => '0', 'uangmakanharian' => '150000', 'pinjamanpribadi' => '0', 'gajiminus' => '0', 'uangJalantidakterhitung' => '0', 'statusformat' => '146', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
        gajisupirheader::create(['nobukti' => 'RIC 0004/II/2023', 'tglbukti' => '2023/2/1', 'keterangan' => '', 'supir_id' => '83', 'nominal' => '75000', 'tgldari' => '2023/1/26', 'tglsampai' => '2023/1/26', 'total' => '75000', 'uangjalan' => '0', 'bbm' => '0', 'potonganpinjaman' => '0', 'deposito' => '0', 'potonganpinjamansemua' => '0', 'komisisupir' => '5000', 'tolsupir' => '0', 'voucher' => '0', 'uangmakanharian' => '0', 'pinjamanpribadi' => '0', 'gajiminus' => '0', 'uangJalantidakterhitung' => '0', 'statusformat' => '146', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'modifiedby' => 'ADMIN',]);
    }
}
