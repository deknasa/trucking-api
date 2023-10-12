<?php

namespace Database\Seeders;

use App\Models\UpahSupir;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpahSupirSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::statement("delete UpahSupir");
        DB::statement("DBCC CHECKIDENT ('UpahSupir', RESEED, 1);");

    }
}
