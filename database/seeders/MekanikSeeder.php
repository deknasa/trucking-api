<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\Mekanik;



class MekanikSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        
        Mekanik::create([
            'namamekanik' => 'SUKIR',
            'keterangan' => 'SUKIR',
            'statusaktif' => 1,
            'modifiedby' => 'ADMIN',
        ]);

        Mekanik::create([
            'namamekanik' => 'YANTO',
            'keterangan' => 'YANTO',
            'statusaktif' => 2,
            'modifiedby' => 'ADMIN',
        ]);

        Mekanik::create([
            'namamekanik' => 'BUDI',
            'keterangan' => 'BUDI',
            'statusaktif' => 3,
            'modifiedby' => 'ADMIN',
        ]);
        
    }
}
