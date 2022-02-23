<?php

namespace Database\Factories;

use App\Models\Parameter;
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
            'kodecabang' => $this->faker->word(2, true),
            'namacabang' => $this->faker->city(),
            'statusaktif' => 1,
        ];
    }
}
