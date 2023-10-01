<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Gudang;
use Illuminate\Support\Facades\DB;

class GudangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete Gudang");
        DB::statement("DBCC CHECKIDENT ('Gudang', RESEED, 1);");

        gudang::create(['gudang' => 'GUDANG KANTOR', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
        gudang::create(['gudang' => 'GUDANG PIHAK III', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
        gudang::create(['gudang' => 'GUDANG SEMENTARA', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
        gudang::create(['gudang' => 'GUDANG GARASI', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
        gudang::create(['gudang' => 'WORK SHOP', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
        gudang::create(['gudang' => 'MEKANIK', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '',]);
    }
}
