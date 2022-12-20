<?php

namespace Database\Seeders;

use App\Models\UpahSupir;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpahSupirSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::statement("delete UpahSupir");
        DB::statement("DBCC CHECKIDENT ('UpahSupir', RESEED, 1);");

        UpahSupir::create(['kotadari_id' => '1', 'kotasampai_id' => '2', 'jarak' => '12', 'zona_id' => '1', 'statusaktif' => '1', 'tglmulaiberlaku' => '2022/12/20', 'tglakhirberlaku' => '2042/12/20', 'statusluarkota' => '201', 'modifiedby' => 'ADMIN',]);
    }
}
