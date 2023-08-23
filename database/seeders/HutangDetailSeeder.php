<?php

namespace Database\Seeders;

use App\Models\HutangDetail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HutangDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete HutangDetail");
        DB::statement("DBCC CHECKIDENT ('HutangDetail', RESEED, 1);");

        
    }
}
