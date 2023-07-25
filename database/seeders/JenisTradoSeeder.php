<?php

namespace Database\Seeders;

use App\Models\JenisTrado;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JenisTradoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete JenisTrado");
        DB::statement("DBCC CHECKIDENT ('JenisTrado', RESEED, 1);");

        jenistrado::create(['kodejenistrado' => 'FUSO 8DC11', 'keterangan' => 'FUSO 8DC11', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        jenistrado::create(['kodejenistrado' => 'FUSO 6D22', 'keterangan' => 'FUSO 6D22', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        jenistrado::create(['kodejenistrado' => 'ALL', 'keterangan' => 'ALL', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        jenistrado::create(['kodejenistrado' => 'SUPER GREAT', 'keterangan' => 'SUPER GREAT', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        jenistrado::create(['kodejenistrado' => 'FUSO 8DC9', 'keterangan' => 'FUSO 8DC9', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        jenistrado::create(['kodejenistrado' => 'GANDENGAN', 'keterangan' => 'GANDENGAN', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        jenistrado::create(['kodejenistrado' => 'NISSAN', 'keterangan' => 'NISSAN', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
    }
}
