<?php

namespace Database\Factories;

use App\Models\Agen;
use App\Models\Bank;
use App\Models\Parameter;
use Illuminate\Database\Eloquent\Factories\Factory;

class PiutangHeaderFactory extends Factory
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
            'keterangan' => $this->faker->sentence(),
            'postingdari' => $this->faker->randomElement(Bank::all()),
            'agen_id' => $this->faker->randomElement(Agen::all()),
            'invoice_nobukti' => $this->faker->word(),
            'statusformat' => $this->faker->randomElement(Parameter::where('grp', 'PIUTANG BUKTI')->get())
        ];
    }
}
