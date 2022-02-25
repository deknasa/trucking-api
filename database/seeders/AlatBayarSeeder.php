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
            'kodealatbayar' => 'TUNAI',
            'namaalatbayar' => 'TUNAI',
            'keterangan' => 'TUNAI',
            'statuslangsunggcair' => 56,
            'statusdefault' => 58,
            'bank_id' => 1,
            'modifiedby' => 'ADMIN',
        ]);
    }
}
