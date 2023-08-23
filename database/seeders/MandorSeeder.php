<?php

namespace Database\Seeders;

use App\Models\Mandor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MandorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete Mandor");
        DB::statement("DBCC CHECKIDENT ('Mandor', RESEED, 1);");

        
    }
}
