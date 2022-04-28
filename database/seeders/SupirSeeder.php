<?php

namespace Database\Seeders;

use App\Models\Supir;
use App\Models\Zona;
use Illuminate\Database\Seeder;

class SupirSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $zonas = Zona::all();
        
        Supir::create([
            'namasupir' => 'HERMAN',
            'alamat' => 'JLN.BILAN NO 5',
            'kota' => 'MEDAN',
            'telp' => '081325885212',
            'statusaktif' => 1,
            'nominaldepositsa' => 0,
            'depositke' => 1,
            'tglmasuk' => '2021-01-05',
            'nominalpinjamansaldoawal' => 0,
            'supirold_id' => 0,
            'tglexpsim' => '2023-05-05',
            'nosim' => '123456789012',
            'keterangan' => '-',
            'noktp' => '20011253568',
            'nokk' => '20011253555',
            'statusadaupdategambar' => 44,
            'statuslluarkota' => 16,
            'statuszonatertentu' => 49,
            'zona_id' => $zonas[rand(0, count($zonas) - 1)]->id,
            'angsuranpinjaman' => 0,
            'plafondeposito' => 0,
            'photosupir' => '',
            'photoktp' => '',
            'photosim' => '',
            'photokk' => '',
            'photoskck' => '',
            'photodomisili' => '',
            'keteranganresign' => '',
            'statusblacklist' => 51,
            'tglberhentisupir' => '1900-01-01',
            'tgllahir' => '1980-01-05',
            'tglterbitsim' => '2021-01-05',
            'modifiedby' => 'ADMIN',
        ]);

        Supir::create([
            'namasupir' => 'ANDIKA',
            'alamat' => 'JLN.BILAL NO 1',
            'kota' => 'MEDAN',
            'telp' => '0813258852SS12',
            'statusaktif' => 1,
            'nominaldepositsa' => 0,
            'depositke' => 1,
            'tglmasuk' => '2021-01-05',
            'nominalpinjamansaldoawal' => 0,
            'supirold_id' => 0,
            'tglexpsim' => '2023-05-05',
            'nosim' => '123456789012',
            'keterangan' => '-',
            'noktp' => '20011253568',
            'nokk' => '20011253555',
            'statusadaupdategambar' => 44,
            'statuslluarkota' => 16,
            'statuszonatertentu' => 49,
            'zona_id' => $zonas[rand(0, count($zonas) - 1)]->id,
            'angsuranpinjaman' => 0,
            'plafondeposito' => 0,
            'photosupir' => '',
            'photoktp' => '',
            'photosim' => '',
            'photokk' => '',
            'photoskck' => '',
            'photodomisili' => '',
            'keteranganresign' => '',
            'statusblacklist' => 51,
            'tglberhentisupir' => '1900-01-01',
            'tgllahir' => '1980-01-05',
            'tglterbitsim' => '2021-01-05',
            'modifiedby' => 'ADMIN',
        ]);
    }
}
