<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Penerima;
use Illuminate\Support\Facades\DB;

class PenerimaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete Penerima");
        DB::statement("DBCC CHECKIDENT ('Penerima', RESEED, 1);");

        Penerima::create(['namapenerima' => 'A.RONI LUBIS', 'npwp' => '', 'noktp' => '12345678', 'statusaktif' => '1', 'statuskaryawan' => '6', 'modifiedby' => 'ADMIN',]);
        Penerima::create(['namapenerima' => 'AMENG AC', 'npwp' => '', 'noktp' => '987456', 'statusaktif' => '1', 'statuskaryawan' => '7', 'modifiedby' => 'ADMIN',]);
    }
}
