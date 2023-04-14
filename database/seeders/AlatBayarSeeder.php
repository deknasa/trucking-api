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

        AlatBAyar::create(['kodealatbayar' => 'TUNAI', 'namaalatbayar' => 'tunai', 'keterangan' => 'tunai', 'statuslangsungcair' => '56', 'statusdefault' => '58', 'statusaktif' => '1', 'bank_id' => '1', 'modifiedby' => 'ADMIN', 'coa' => '01.01.01.02',]);
        AlatBAyar::create(['kodealatbayar' => 'TRANSFER', 'namaalatbayar' => 'TRANSFER', 'keterangan' => 'TRANSFER', 'statuslangsungcair' => '56', 'statusdefault' => '58', 'statusaktif' => '1', 'bank_id' => '2', 'modifiedby' => 'ADMIN', 'coa' => '01.02.02.01',]);
        AlatBAyar::create(['kodealatbayar' => 'GIRO', 'namaalatbayar' => 'GIRO', 'keterangan' => '1', 'statuslangsungcair' => '57', 'statusdefault' => '59', 'statusaktif' => '1', 'bank_id' => '2', 'modifiedby' => 'ADMIN', 'coa' => '03.02.02.05',]);
        AlatBAyar::create(['kodealatbayar' => 'TRANSFER', 'namaalatbayar' => 'TRANSFER', 'keterangan' => 'TRANSFER', 'statuslangsungcair' => '56', 'statusdefault' => '58', 'statusaktif' => '1', 'bank_id' => '3', 'modifiedby' => 'ADMIN', 'coa' => '01.02.02.01',]);
        AlatBAyar::create(['kodealatbayar' => 'GIRO', 'namaalatbayar' => 'GIRO', 'keterangan' => '1', 'statuslangsungcair' => '57', 'statusdefault' => '59', 'statusaktif' => '1', 'bank_id' => '3', 'modifiedby' => 'ADMIN', 'coa' => '03.02.02.05',]);
        AlatBAyar::create(['kodealatbayar' => 'TRANSFER', 'namaalatbayar' => 'TRANSFER', 'keterangan' => 'TRANSFER', 'statuslangsungcair' => '56', 'statusdefault' => '58', 'statusaktif' => '1', 'bank_id' => '4', 'modifiedby' => 'ADMIN', 'coa' => '01.02.02.01',]);
        AlatBAyar::create(['kodealatbayar' => 'GIRO', 'namaalatbayar' => 'GIRO', 'keterangan' => '1', 'statuslangsungcair' => '57', 'statusdefault' => '59', 'statusaktif' => '1', 'bank_id' => '4', 'modifiedby' => 'ADMIN', 'coa' => '03.02.02.05',]);
        AlatBAyar::create(['kodealatbayar' => 'TRANSFER', 'namaalatbayar' => 'TRANSFER', 'keterangan' => 'TRANSFER', 'statuslangsungcair' => '56', 'statusdefault' => '58', 'statusaktif' => '1', 'bank_id' => '5', 'modifiedby' => 'ADMIN', 'coa' => '01.02.02.01',]);
        AlatBAyar::create(['kodealatbayar' => 'GIRO', 'namaalatbayar' => 'GIRO', 'keterangan' => '1', 'statuslangsungcair' => '57', 'statusdefault' => '59', 'statusaktif' => '1', 'bank_id' => '5', 'modifiedby' => 'ADMIN', 'coa' => '03.02.02.05',]);
       


    }
}
