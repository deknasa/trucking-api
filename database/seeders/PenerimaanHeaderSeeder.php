<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PenerimaanHeader;
use Illuminate\Support\Facades\DB;

class PenerimaanHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete penerimaanheader");
        DB::statement("DBCC CHECKIDENT ('penerimaanheader', RESEED, 1);");


    }
}
