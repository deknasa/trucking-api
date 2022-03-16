<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AgenFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'kodeagen' => $this->faker->word(),
            'namaagen' => $this->faker->word(),
            'keterangan' => $this->faker->word(),
            'statusaktif' => 1,
            'namaperusahaan' => $this->faker->company(),
            'alamat' => $this->faker->address(),
            'notelp' => $this->faker->word(),
            'nohp' => $this->faker->phoneNumber(),
            'contactperson' => $this->faker->firstName(),
            'top' => 1,
            'statusapproval' => 1,
            'userapproval' => $this->faker->word(),
            'tglapproval' => $this->faker->date(),
            'statustas' => 1,
            'jenisemkl' => $this->faker->word(),
            'modifiedby' => 'ADMIN',
        ];
    }
}
