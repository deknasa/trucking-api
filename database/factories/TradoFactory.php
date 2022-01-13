<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TradoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'keterangan' => $this->faker->words(2, true),
            'statusaktif' => 1,
            'kmawal' => 1,
            'kmakhirgantioli' => 1,
            'tglakhirgantioli' => $this->faker->date(),
            'tglstnkmati' => $this->faker->date(),
            'tglasuransimati' => $this->faker->date(),
            'tahun' => $this->faker->words(2, true),
            'lastproduksi' => $this->faker->words(2, true),
            'merek' => $this->faker->words(2, true),
            'norangka' => $this->faker->words(2, true),
            'nomesin' => $this->faker->words(2, true),
            'nama' => $this->faker->words(2, true),
            'nostnk' => $this->faker->words(2, true),
            'alamatstnk' => $this->faker->words(2, true),
            'modifiedby' => $this->faker->words(2, true),
            'tglstandarisasi' => $this->faker->date(),
            'tglserviceopname' => $this->faker->date(),
            'statusstandarisasi' => 1,
            'keteranganprogressstandarisasi' => $this->faker->words(2, true),
            'jenisplat' => 1,
            'tglspeksimati' => $this->faker->date(),
            'tglpajakstnk' => $this->faker->date(),
            'tglgantiakiterakhir' => $this->faker->date(),
            'statusmutasi' => 1,
            'statusvalidasikendaraan' => 1,
            'tipe' => $this->faker->words(2, true),
            'jenis' => $this->faker->words(2, true),
            'isisilinder' => 1,
            'warna' => $this->faker->words(2, true),
            'bahanbakar' => $this->faker->words(2, true),
            'jlhsumbu' => 1,
            'jlhroda' => 1,
            'model' => $this->faker->words(2, true),
            'nobpkb' => $this->faker->words(2, true),
            'statusmobilstoring' => 1,
            'mandor_id' => 1,
            'jlhbanserap' => 1,
            'statusappeditban' => 1,
            'statuslewatvalidasi' => 1,
            'photostnk' => $this->faker->words(2, true),
            'photobpkb' => $this->faker->words(2, true),
            'phototrado' => $this->faker->words(2, true),
        ];
    }
}
