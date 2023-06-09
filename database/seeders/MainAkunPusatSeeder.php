<?php

namespace Database\Seeders;


use App\Models\MainAkunPusat;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MainAkunPusatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete MainAkunpusat");
        DB::statement("DBCC CHECKIDENT ('MainAkunpusat', RESEED, 1);");
    }
}
