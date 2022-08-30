<?php

namespace Database\Factories;

use App\Models\User;
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
      'nobukti' => $this->faker->word(),
      'tglbukti' => $this->faker->date(),
      'keterangan' => $this->faker->word(),
      'kasgantung_nobukti' => $this->faker->word(),
    ];
  }
}
