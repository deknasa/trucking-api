<?php

namespace Database\Factories;

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
            'modifiedby' => rand(0, 1) == 0 ? 'ADMIN' : 'GUEST',
        ];
    }
}
