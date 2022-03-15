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
            'kodeabsen' => '111',
            'keterangan' => $this->faker->words(5, true),
            'statusaktif' => 1,
            'modifiedby' => 'admin',
        ];
    }
}
