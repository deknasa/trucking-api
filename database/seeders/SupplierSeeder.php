<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete Supplier");
        DB::statement("DBCC CHECKIDENT ('Supplier', RESEED, 1);");

        Supplier::create([ 'namasupplier' => 'SAUDARA MOTOR', 'namakontak' => 'PAU AGUSTIA LIUS', 'alamat' => 'JL.TILAK NO.10 KEL.SEI RENGAS I KEC.MEDAN KOTA', 'kota' => 'MEDAN', 'kodepos' => '20214', 'notelp1' => '061-7346939', 'notelp2' => '', 'email' => 'PL_LIUS@YAHOO.COM', 'statusaktif' => '1', 'web' => '', 'namapemilik' => 'PAU AGUSTIA LIUS', 'jenisusaha' => 'SPAREPARTS', 'top' => '18', 'bank' => 'BCA', 'rekeningbank' => '1950923268', 'namarekening' => 'MARIA AMRIN/PAUL AGUSTIA LIUS', 'jabatan' => 'PEMILIK', 'statusdaftarharga' => '96', 'kategoriusaha' => 'PENGECER', 'modifiedby' => 'ADMIN',]);
    }
}
