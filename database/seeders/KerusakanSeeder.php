<?php

namespace Database\Seeders;
use App\Models\Kerusakan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KerusakanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete kerusakan");
        DB::statement("DBCC CHECKIDENT ('kerusakan', RESEED, 1);");

      
    }
}
