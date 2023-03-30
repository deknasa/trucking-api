<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PenerimaanStokDetail;
use Illuminate\Support\Facades\DB;

class PenerimaanStokDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {



        DB::statement("delete PenerimaanStokDetail");
        DB::statement("DBCC CHECKIDENT ('PenerimaanStokDetail', RESEED, 1);");
        
        
    }
}
