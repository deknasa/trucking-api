<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ZonaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'zona' => $this->faker->city(),
            'keterangan' => $this->faker->words(2, true),
            'modifiedby' => 'admin',
        ];
    }
}
