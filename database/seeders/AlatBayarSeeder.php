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

        alatbayar::create([ 'kodealatbayar' => 'TUNAI', 'namaalatbayar' => 'TUNAI', 'keterangan' => 'TUNAI', 'statuslangsungcair' => '56', 'statusdefault' => '58', 'coa' => '01.01.01.02', 'bank_id' => '1', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '', 'tipe' => 'KAS',]);
        alatbayar::create([ 'kodealatbayar' => 'TRANSFER', 'namaalatbayar' => 'TRANSFER', 'keterangan' => 'TRANSFER', 'statuslangsungcair' => '56', 'statusdefault' => '58', 'coa' => '01.02.02.01', 'bank_id' => '2', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '', 'tipe' => 'BANK',]);
        alatbayar::create([ 'kodealatbayar' => 'GIRO', 'namaalatbayar' => 'GIRO', 'keterangan' => '1', 'statuslangsungcair' => '57', 'statusdefault' => '59', 'coa' => '03.02.02.05', 'bank_id' => '2', 'statusaktif' => '1', 'modifiedby' => 'ADMIN', 'info' => '', 'tipe' => 'BANK',]);      


    }
}
