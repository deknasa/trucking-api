<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\KasGantungHeader;
use Illuminate\Support\Facades\DB;

class KasGantungHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete KasGantungHeader");
        DB::statement("DBCC CHECKIDENT ('KasGantungHeader', RESEED, 1);");

        
    }
}
