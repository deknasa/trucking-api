<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ErrorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'kodeerror' => $this->faker->word(),
            'keterangan' => $this->faker->words(3, true)
        ];
    }
}
