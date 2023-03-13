<?php

namespace Database\Seeders;

use App\Models\Zona;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ZonaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete zona");
        DB::statement("DBCC CHECKIDENT ('zona', RESEED, 1);");


        Zona::create(['zona' => 'ZONA I', 'keterangan' => 'ZONA I', 'modifiedby' => 'RYAN', 'statusaktif' => '1',]);
        Zona::create(['zona' => 'ZONA II', 'keterangan' => 'ZONA II', 'modifiedby' => 'RYAN', 'statusaktif' => '1',]);
        Zona::create(['zona' => 'ZONA III', 'keterangan' => 'ZONA III', 'modifiedby' => 'RYAN', 'statusaktif' => '1',]);
        Zona::create(['zona' => 'ZONA IV', 'keterangan' => 'ZONA IV', 'modifiedby' => 'RYAN', 'statusaktif' => '1',]);
        Zona::create(['zona' => 'ZONA V', 'keterangan' => 'ZONA V', 'modifiedby' => 'RYAN', 'statusaktif' => '1',]);
        Zona::create(['zona' => 'ZONA VI', 'keterangan' => 'ZONA VI', 'modifiedby' => 'RYAN', 'statusaktif' => '1',]);
        Zona::create(['zona' => 'ZONA VII', 'keterangan' => 'ZONA VII', 'modifiedby' => 'RYAN', 'statusaktif' => '1',]);
    }
}
