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
            'grp' => $this->faker->name(),
            'subgrp' => $this->faker->name(),
            'text' => $this->faker->name(),
            'memo' => $this->faker->name(),
            'modifiedby' => $this->faker->randomElement(User::all()->pluck('user'))
        ];
    }
}
