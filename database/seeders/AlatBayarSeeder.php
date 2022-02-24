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
            'namaalatbayar' => '',
            'langsunggcair' => '',
            'default' => '',
            'bank_id' => '',
            'modifiedby' => 'ADMIN',
        ]);
    }
}
