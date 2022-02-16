<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MandorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'namamandor' => $this->faker->words(2, true),
            'keterangan' => $this->faker->name(),
            'statusaktif' => 1,
            'modifiedby' => 'admin',
        ];
    }
}
