<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AbsensiSupirHeaderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'nobukti' => $this->faker->name(),
            'tgl' => $this->faker->date(),
            'keterangan' => $this->faker->name(),
            'kasgantung_nobukti' => $this->faker->name(),
            'nominal' => $this->faker->randomNumber(),
            'modifiedby' => $this->faker->name(),
        ];
    }
}
