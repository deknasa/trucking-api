<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AkunPusatFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'coa' => '01.01.01.02',
            'keterangancoa' => 'KAS - FISIK MEDAN',
            'type' => 'KAS',
            'level' => '3',
            'aktif' => '1',
            'parent' => '01.01.01.00',
            'statusaccountpayable' => '34',
            'statusneraca' => '36',
            'statuslabarugi' => '38',
            'coamain' => '01.01.01.02',
            'modifiedby' => 'ADMIN',
        ];
    }
}
