<?php

namespace Database\Seeders;
use App\Models\AlatBayar;
use Illuminate\Database\Seeder;

class AlatBayarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        AlatBayar::create([
            'kodealatbayar' => 'TRANSFER',
            'namaalatbayar' => 'TRANSFER',
            'keterangan' => 'TRANSFER',
            'statuslangsunggcair' => 45,
            'statusdefault' => 21,
            'bank_id' => 2,
            'modifiedby' => 'ADMIN',
        ]);


    }
}
