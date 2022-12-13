<?php

namespace Database\Seeders;

use App\Models\Supir;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupirSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete Supir");
        DB::statement("DBCC CHECKIDENT ('Supir', RESEED, 1);");

        supir::create(['namasupir' => 'HERMAN', 'alamat' => 'JLN.BILAN NO 5', 'kota' => 'MEDAN', 'telp' => '081325885212', 'statusaktif' => '1', 'nominaldepositsa' => '0', 'depositke' => '1', 'tglmasuk' => '2021/1/5', 'nominalpinjamansaldoawal' => '0', 'supirold_id' => '0', 'tglexpsim' => '2023/5/5', 'nosim' => '123456789012', 'keterangan' => '-', 'noktp' => '20011253568', 'nokk' => '20011253555', 'statusadaupdategambar' => '44', 'statusluarkota' => '46', 'statuszonatertentu' => '49', 'zona_id' => '0', 'angsuranpinjaman' => '0', 'plafondeposito' => '0', 'photosupir' => '', 'photoktp' => '', 'photosim' => '', 'photokk' => '', 'photoskck' => '', 'photodomisili' => '', 'keteranganresign' => '', 'statusblacklist' => '51', 'tglberhentisupir' => '1900/1/1', 'tgllahir' => '1980/1/5', 'tglterbitsim' => '2021/1/5', 'modifiedby' => 'ADMIN',]);
        supir::create(['namasupir' => 'ANDIKA', 'alamat' => 'JLN.BILAL NO 1', 'kota' => 'MEDAN', 'telp' => '0813258852SS12', 'statusaktif' => '1', 'nominaldepositsa' => '0', 'depositke' => '1', 'tglmasuk' => '2021/1/5', 'nominalpinjamansaldoawal' => '0', 'supirold_id' => '0', 'tglexpsim' => '2023/5/5', 'nosim' => '123456789012', 'keterangan' => '-', 'noktp' => '20011253568', 'nokk' => '20011253555', 'statusadaupdategambar' => '44', 'statusluarkota' => '47', 'statuszonatertentu' => '49', 'zona_id' => '0', 'angsuranpinjaman' => '0', 'plafondeposito' => '0', 'photosupir' => '', 'photoktp' => '', 'photosim' => '', 'photokk' => '', 'photoskck' => '', 'photodomisili' => '', 'keteranganresign' => '', 'statusblacklist' => '51', 'tglberhentisupir' => '1900/1/1', 'tgllahir' => '1980/1/5', 'tglterbitsim' => '2021/1/5', 'modifiedby' => 'ADMIN',]);
    }
}
