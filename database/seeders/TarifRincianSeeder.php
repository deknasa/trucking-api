<?php

namespace Database\Seeders;

use App\Models\TarifRincian;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TarifRincianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete TarifRincian");
        DB::statement("DBCC CHECKIDENT ('TarifRincian', RESEED, 1);");

        tarifrincian::create(['tarif_id' => '1', 'container_id' => '1', 'nominal' => '600000', 'modifiedby' => 'ADMIN',]);
        tarifrincian::create(['tarif_id' => '1', 'container_id' => '2', 'nominal' => '750000', 'modifiedby' => 'ADMIN',]);
        tarifrincian::create(['tarif_id' => '1', 'container_id' => '3', 'nominal' => '800000', 'modifiedby' => 'ADMIN',]);
    }
}
