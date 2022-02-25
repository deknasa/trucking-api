<?php

namespace Database\Seeders;
use App\Models\UpahSupir;
use Illuminate\Database\Seeder;

class UpahSupirSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //

        UpahSupir::create([
            'kotadari_id' => 1,
            'kotasampai_id' => 2,
            'jarak' => 30,
            'zona_id' => 1,
            'statusaktif' => 1,
            'tglmulaiberlaku' => '2021-01-01',
            'statusluarkota' => 61,
            'modifiedby' => 'ADMIN',
        ]);

        UpahSupir::create([
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
