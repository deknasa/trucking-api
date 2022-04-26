<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'namasupplier' => $this->faker->words(5, true),
            'namakontak' => $this->faker->words(5, true),
            'alamat' => $this->faker->words(5, true),
            'coa_id' => 1,
            'kota' => $this->faker->words(5, true),
            'kodepos' => $this->faker->words(5, true),
            'notelp1' => $this->faker->phoneNumber(),
            'notelp2' => $this->faker->phoneNumber(),
            'email' => $this->faker->words(5, true),
            'statussupllier' => $this->faker->randomNumber(),
            'web' => $this->faker->words(5, true),
            'namapemilik' => $this->faker->words(5, true),
            'jenisusaha' => $this->faker->words(5, true),
            'top' => $this->faker->randomNumber(),
            'bank' => $this->faker->words(5, true),
            'rekeningbank' => $this->faker->words(5, true),
            'namabank' => $this->faker->words(5, true),
            'jabatan' => $this->faker->words(5, true),
            'statusdaftarharga' => $this->faker->randomNumber(),
            'kategoriusaha' => $this->faker->words(5, true),
            'bataskredit' => $this->faker->randomFloat(),
            'modifiedby' => 'ADMIN',
        ];
    }
}
