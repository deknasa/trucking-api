<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CabangFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'kodecabang' => $this->faker->name(),
            'namacabang' => $this->faker->name(),
            'statusaktif' => $this->faker->name(),
        ];
    }
}
