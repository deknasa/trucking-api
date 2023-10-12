<?php

namespace Database\Seeders;

use App\Models\UpahSupirRincian;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpahSupirRincianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete UpahSupirrincian");
        DB::statement("DBCC CHECKIDENT ('UpahSupirrincian', RESEED, 1);");
    }
}
