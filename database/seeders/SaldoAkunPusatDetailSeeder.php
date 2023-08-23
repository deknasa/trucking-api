<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SaldoAkunPusatDetail;
use Illuminate\Support\Facades\DB;

class SaldoAkunPusatDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()

    
    {

        DB::statement("delete saldoakunpusatdetail");
        DB::statement("DBCC CHECKIDENT ('saldoakunpusatdetail', RESEED, 1);");
        
    }
}
