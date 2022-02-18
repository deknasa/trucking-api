<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BankFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'kodebank' => 'KAS TRUCKING',
            'namabank' => 'KAS TRUCKING',
            'coa' => '01.01.01.02',
            'tipe' => 'KAS',
            'statusaktif' => '1',
            'kodepenerimaan' => '32',
            'kodepengeluaran' => '33',
            'modifiedby' => 'ADMIN',
        ];
       
    }
}
