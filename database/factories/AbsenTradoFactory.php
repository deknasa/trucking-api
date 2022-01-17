<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AbsenTradoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'nabsen' => $this->faker->words(2, true),
            'keterangan' => $this->faker->words(2, true),
            'statusaktif' => 1,
            'modifiedby' => $this->faker->words(2, true),
        ];
    }
}
