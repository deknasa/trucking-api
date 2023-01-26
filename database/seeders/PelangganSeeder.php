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

        Pelanggan::create([ 'kodepelanggan' => '3 POWER', 'namapelanggan' => '3 POWER', 'statusaktif' => '1', 'keterangan' => '', 'telp' => '082161573038', 'alamat' => 'JLN. PERDAMAIAN NO 10', 'alamat2' => '', 'kota' => 'MEDAN', 'kodepos' => '20221', 'modifiedby' => 'ADMIN',]);
    }
}
