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
        $parameters = Parameter::all()->pluck('id');

        return [
            'cabang' => $this->faker->name(),
            'statusaktif' => $this->faker->randomElement($parameters),
        ];
    }
}
