<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UpahRitasi;

class UpahRitasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        UpahRitasi::create([
            'kotadari_id' => 1,
            'kotasampai_id' => 2,
            'jarak' => 30,
            'zona_id' => 1,
            'statusaktif' => 1,
            'tglmulaiberlaku' => '2021-01-01',
            'statusluarkota' => 61,
            'modifiedby' => 'ADMIN',
        ]);

        UpahRitasi::create([
            'kotadari_id' => 1,
            'kotasampai_id' => 3,
            'jarak' => 30,
            'zona_id' => 1,
            'statusaktif' => 1,
            'tglmulaiberlaku' => '2021-01-01',
            'statusluarkota' => 61,
            'modifiedby' => 'ADMIN',
        ]);
    }
}
