<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Mekanik;
use Illuminate\Support\Facades\DB;


class MekanikSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete Mekanik");
        DB::statement("DBCC CHECKIDENT ('Mekanik', RESEED, 1);");

        Mekanik::create(['namamekanik' => 'SUKIR', 'keterangan' => 'SUKIR', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        Mekanik::create(['namamekanik' => 'UDIN', 'keterangan' => 'JUNIOR 1', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
    }
}
