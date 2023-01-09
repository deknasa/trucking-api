<?php

namespace Database\Seeders;

use App\Models\AlatBayar;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AlatBayarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete AlatBayar");
        DB::statement("DBCC CHECKIDENT ('AlatBayar', RESEED, 1);");

        AlatBAyar::create(['kodealatbayar' => 'TRANSFER', 'namaalatbayar' => 'TRANSFER', 'keterangan' => 'TRANSFER', 'statuslangsungcair' => '45', 'statusdefault' => '21', 'bank_id' => '2', 'modifiedby' => 'ADMIN', 'coa' => '',]);
        AlatBAyar::create(['kodealatbayar' => 'GIRO', 'namaalatbayar' => 'GIRO', 'keterangan' => '1', 'statuslangsungcair' => '57', 'statusdefault' => '59', 'bank_id' => '2', 'modifiedby' => 'ADMIN', 'coa' => '03.02.02.05',]);
    }
}
