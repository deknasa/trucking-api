<?php

namespace Database\Seeders;

use App\Models\JenisEmkl;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JenisEmklSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete JenisEmkl");
        DB::statement("DBCC CHECKIDENT ('JenisEmkl', RESEED, 1);");

        jenisemkl::create(['kodejenisemkl' => 'TAS', 'keterangan' => 'EMKL TAS', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        jenisemkl::create(['kodejenisemkl' => 'OL', 'keterangan' => 'ORDERAN LUAR', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
    }
}
