<?php

namespace Database\Seeders;

use App\Models\HutangHeader;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HutangHeaderSeeder extends Seeder
{
    /**git 
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete HutangHeader");
        DB::statement("DBCC CHECKIDENT ('HutangHeader', RESEED, 1);");

            }
}
