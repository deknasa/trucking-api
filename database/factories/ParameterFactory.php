<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ParameterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'grp' => $this->faker->word(),
            'subgrp' => $this->faker->word(),
            'text' => $this->faker->word(),
            'memo' => $this->faker->word(),
            'modifiedby' => $this->faker->randomElement(User::all()->pluck('user'))
        ];
    }
}
