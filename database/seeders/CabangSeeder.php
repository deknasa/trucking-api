<?php

namespace Database\Seeders;

use App\Models\Cabang;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CabangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete cabang");
        DB::statement("DBCC CHECKIDENT ('cabang', RESEED, 1);");

        cabang::create(['kodecabang' => 'PST',  'namacabang' => 'PUSAT',  'statusaktif' => '1',  'modifiedby' => 'ADMIN',]);
        cabang::create(['kodecabang' => 'MDN',  'namacabang' => 'MEDAN',  'statusaktif' => '1',  'modifiedby' => 'ADMIN',]);
        cabang::create(['kodecabang' => 'JKT',  'namacabang' => 'JAKARTA',  'statusaktif' => '1',  'modifiedby' => 'ADMIN',]);
        cabang::create(['kodecabang' => 'SBY',  'namacabang' => 'SURABAYA',  'statusaktif' => '1',  'modifiedby' => 'ADMIN',]);
        cabang::create(['kodecabang' => 'MKS',  'namacabang' => 'MAKASSAR',  'statusaktif' => '1',  'modifiedby' => 'ADMIN',]);
        cabang::create(['kodecabang' => 'BTG',  'namacabang' => 'BITUNG',  'statusaktif' => '1',  'modifiedby' => 'ADMIN',]);
        cabang::create(['kodecabang' => 'TNL',  'namacabang' => 'JAKARTA TNL',  'statusaktif' => '1',  'modifiedby' => 'ADMIN',]);
    }
}
