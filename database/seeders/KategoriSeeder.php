<?php

namespace Database\Seeders;

use App\Models\Kategori;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KategoriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete Kategori");
        DB::statement("DBCC CHECKIDENT ('Kategori', RESEED, 1);");

    }
}
