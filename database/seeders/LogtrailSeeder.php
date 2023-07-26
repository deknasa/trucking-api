<?php

namespace Database\Seeders;

use App\Models\logtrail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LogtrailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete logtrail");
        DB::statement("DBCC CHECKIDENT ('logtrail', RESEED, 1);");

    }
}
