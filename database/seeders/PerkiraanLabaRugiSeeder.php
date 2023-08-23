<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PerkiraanLabaRugi;
use Illuminate\Support\Facades\DB;

class PerkiraanLabaRugiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete perkiraanlabarugi");
        DB::statement("DBCC CHECKIDENT ('perkiraanlabarugi', RESEED, 1);");

    }
    
}
