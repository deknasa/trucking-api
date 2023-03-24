<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GajiSupirPelunasanPinjaman;
use Illuminate\Support\Facades\DB;

class GajiSupirPelunasanPinjamanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete GajiSupirPelunasanPinjaman");
        DB::statement("DBCC CHECKIDENT ('GajiSupirPelunasanPinjaman', RESEED, 1);");

        gajisupirpelunasanpinjaman::create(['gajisupir_id' => '10', 'gajisupir_nobukti' => 'RIC 0010/II/2023', 'penerimaantrucking_nobukti' => 'PJP 0001/II/2023', 'pengeluarantrucking_nobukti' => 'PJT 0018/III/2022', 'supir_id' => '172', 'nominal' => '4166', 'modifiedby' => 'ADMIN',]);
        gajisupirpelunasanpinjaman::create(['gajisupir_id' => '10', 'gajisupir_nobukti' => 'RIC 0010/II/2023', 'penerimaantrucking_nobukti' => 'PJP 0001/II/2023', 'pengeluarantrucking_nobukti' => 'PJT 0022/VII/2022', 'supir_id' => '172', 'nominal' => '4166', 'modifiedby' => 'ADMIN',]);
        gajisupirpelunasanpinjaman::create(['gajisupir_id' => '10', 'gajisupir_nobukti' => 'RIC 0010/II/2023', 'penerimaantrucking_nobukti' => 'PJP 0001/II/2023', 'pengeluarantrucking_nobukti' => 'PJT 0023/VII/2022', 'supir_id' => '172', 'nominal' => '4166', 'modifiedby' => 'ADMIN',]);
        gajisupirpelunasanpinjaman::create(['gajisupir_id' => '10', 'gajisupir_nobukti' => 'RIC 0010/II/2023', 'penerimaantrucking_nobukti' => 'PJP 0001/II/2023', 'pengeluarantrucking_nobukti' => 'PJT 0006/IX/2015', 'supir_id' => '172', 'nominal' => '4170', 'modifiedby' => 'ADMIN',]);
        gajisupirpelunasanpinjaman::create(['gajisupir_id' => '10', 'gajisupir_nobukti' => 'RIC 0010/II/2023', 'penerimaantrucking_nobukti' => 'PJP 0001/II/2023', 'pengeluarantrucking_nobukti' => 'PJT 0014/I/2019', 'supir_id' => '172', 'nominal' => '4166', 'modifiedby' => 'ADMIN',]);
        gajisupirpelunasanpinjaman::create(['gajisupir_id' => '10', 'gajisupir_nobukti' => 'RIC 0010/II/2023', 'penerimaantrucking_nobukti' => 'PJP 0001/II/2023', 'pengeluarantrucking_nobukti' => 'PJT 0070/VIII/2019', 'supir_id' => '172', 'nominal' => '4166', 'modifiedby' => 'ADMIN',]);
        gajisupirpelunasanpinjaman::create(['gajisupir_id' => '10', 'gajisupir_nobukti' => 'RIC 0010/II/2023', 'penerimaantrucking_nobukti' => 'PJP 0002/II/2023', 'pengeluarantrucking_nobukti' => 'PJT 0016/III/2018', 'supir_id' => '0', 'nominal' => '20000', 'modifiedby' => 'ADMIN',]);
        gajisupirpelunasanpinjaman::create(['gajisupir_id' => '12', 'gajisupir_nobukti' => 'RIC 0012/II/2023', 'penerimaantrucking_nobukti' => 'PJP 0003/II/2023', 'pengeluarantrucking_nobukti' => 'PJT 0020/I/2023', 'supir_id' => '298', 'nominal' => '400000', 'modifiedby' => 'ADMIN',]);
        gajisupirpelunasanpinjaman::create(['gajisupir_id' => '13', 'gajisupir_nobukti' => 'RIC 0013/II/2023', 'penerimaantrucking_nobukti' => 'PJP 0004/II/2023', 'pengeluarantrucking_nobukti' => 'PJT 0014/X/2022', 'supir_id' => '305', 'nominal' => '25000', 'modifiedby' => 'ADMIN',]);
        gajisupirpelunasanpinjaman::create(['gajisupir_id' => '14', 'gajisupir_nobukti' => 'RIC 0014/II/2023', 'penerimaantrucking_nobukti' => 'PJP 0005/II/2023', 'pengeluarantrucking_nobukti' => 'PJT 0002/XII/2022', 'supir_id' => '307', 'nominal' => '12500', 'modifiedby' => 'ADMIN',]);
        gajisupirpelunasanpinjaman::create(['gajisupir_id' => '14', 'gajisupir_nobukti' => 'RIC 0014/II/2023', 'penerimaantrucking_nobukti' => 'PJP 0005/II/2023', 'pengeluarantrucking_nobukti' => 'PJT 0062/XI/2022', 'supir_id' => '307', 'nominal' => '12500', 'modifiedby' => 'ADMIN',]);
        gajisupirpelunasanpinjaman::create(['gajisupir_id' => '15', 'gajisupir_nobukti' => 'RIC 0015/II/2023', 'penerimaantrucking_nobukti' => 'PJP 0006/II/2023', 'pengeluarantrucking_nobukti' => 'PJT 0001/XII/2022', 'supir_id' => '73', 'nominal' => '1250', 'modifiedby' => 'ADMIN',]);
        gajisupirpelunasanpinjaman::create(['gajisupir_id' => '15', 'gajisupir_nobukti' => 'RIC 0015/II/2023', 'penerimaantrucking_nobukti' => 'PJP 0006/II/2023', 'pengeluarantrucking_nobukti' => 'PJT 0004/VIII/2022', 'supir_id' => '73', 'nominal' => '1250', 'modifiedby' => 'ADMIN',]);
        gajisupirpelunasanpinjaman::create(['gajisupir_id' => '15', 'gajisupir_nobukti' => 'RIC 0015/II/2023', 'penerimaantrucking_nobukti' => 'PJP 0006/II/2023', 'pengeluarantrucking_nobukti' => 'PJT 0037/X/2022', 'supir_id' => '73', 'nominal' => '1250', 'modifiedby' => 'ADMIN',]);
        gajisupirpelunasanpinjaman::create(['gajisupir_id' => '15', 'gajisupir_nobukti' => 'RIC 0015/II/2023', 'penerimaantrucking_nobukti' => 'PJP 0006/II/2023', 'pengeluarantrucking_nobukti' => 'PJT 0049/XII/2022', 'supir_id' => '73', 'nominal' => '1250', 'modifiedby' => 'ADMIN',]);
        gajisupirpelunasanpinjaman::create(['gajisupir_id' => '16', 'gajisupir_nobukti' => 'RIC 0016/II/2023', 'penerimaantrucking_nobukti' => 'PJP 0007/II/2023', 'pengeluarantrucking_nobukti' => 'PJT 0021/XII/2022', 'supir_id' => '146', 'nominal' => '50000', 'modifiedby' => 'ADMIN',]);
        gajisupirpelunasanpinjaman::create(['gajisupir_id' => '17', 'gajisupir_nobukti' => 'RIC 0017/II/2023', 'penerimaantrucking_nobukti' => 'PJP 0008/II/2023', 'pengeluarantrucking_nobukti' => 'PJT 0016/III/2018', 'supir_id' => '0', 'nominal' => '20000', 'modifiedby' => 'ADMIN',]);
    }
}
