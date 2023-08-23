<?php

namespace Database\Seeders;

use App\Models\Agen;
use App\Models\Parameter;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AgenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete Agen");
        DB::statement("DBCC CHECKIDENT ('Agen', RESEED, 1);");


    }
}
