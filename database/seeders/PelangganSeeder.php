<?php

namespace Database\Seeders;
use App\Models\Pelanggan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PelangganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete Pelanggan");
        DB::statement("DBCC CHECKIDENT ('Pelanggan', RESEED, 1);");


    }
}
