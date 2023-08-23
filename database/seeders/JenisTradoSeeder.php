<?php

namespace Database\Seeders;

use App\Models\JenisTrado;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JenisTradoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete JenisTrado");
        DB::statement("DBCC CHECKIDENT ('JenisTrado', RESEED, 1);");

      
    }
}
