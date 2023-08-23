<?php

namespace Database\Seeders;

use App\Models\Merk;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MerkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete Merk");
        DB::statement("DBCC CHECKIDENT ('Merk', RESEED, 1);");

        }
}
