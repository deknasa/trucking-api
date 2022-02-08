<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SupirFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'namasupir' => $this->faker->words(2, true),
            'alamat' => $this->faker->words(2, true),
            'kota' => $this->faker->words(2, true),
            'telp' => $this->faker->words(2, true),
            'statusaktif' => 1,
            'nominaldepositsa' => 1,
            'depositke' => 1,
            'tgl' => $this->faker->date(),
            'nominalpinjamansaldoawal' => 1,
            'supirold_id' => 1,
            'tglexpsim' => $this->faker->date(),
            'nosim' => $this->faker->words(2, true),
            'keterangan' => $this->faker->words(2, true),
            'noktp' => $this->faker->words(2, true),
            'nokk' => $this->faker->words(2, true),
            'statusadaupdategambar' => 1,
            'statuslluarkota' => 1,
            'statuszonatertentu' => 1,
            'zona' => 1,
            'angsuranpinjaman' => 1,
            'plafondeposito' => 1,
            'photosupir' => $this->faker->words(2, true),
            'photoktp' => $this->faker->words(2, true),
            'photosim' => $this->faker->words(2, true),
            'photokk' => $this->faker->words(2, true),
            'photoskck' => $this->faker->words(2, true),
            'photodomisili' => $this->faker->words(2, true),
            'keteranganresign' => $this->faker->words(2, true),
            'statuspameran' => 1,
            'statusbacklist' => 1,
            'tglberhentisupir' => $this->faker->date(),
            'tgllahir' => $this->faker->date(),
            'tglterbitsim' => $this->faker->date(),
            'modifiedby' => $this->faker->words(2, true),
        ];
    }
}
