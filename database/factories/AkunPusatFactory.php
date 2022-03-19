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
            'coa' => $this->faker->word(),
            'keterangancoa' => $this->faker->word(),
            'type' => $this->faker->word(),
            'level' => 1,
            'aktif' => 1,
            'parent' => $this->faker->word(),
            'statuscoa' => 1,
            'statusaccountpayable' => 1,
            'statusneraca' => 1,
            'statuslabarugi' => 1,
            'coamain' => $this->faker->word(),
            'modifiedby' => 'ADMIN',
        ];
    }
}
