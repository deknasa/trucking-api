<?php

namespace Database\Factories;

use App\Models\AkunPusat;
use App\Models\Parameter;
use Illuminate\Database\Eloquent\Factories\Factory;

class BankFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'kodebank' => $this->faker->word(),
            'namabank' => $this->faker->word(),
            'coa' => $this->faker->randomElement(AkunPusat::all()->pluck('coa')),
            'tipe' => $this->faker->word(),
            'statusaktif' => $this->faker->randomElement(Parameter::where('grp', 'STATUS AKTIF')->get()),
            'formatpenerimaan' => $this->faker->randomElement(Parameter::where('grp', 'PENERIMAAN KAS')->get()),
            'formatpengeluaran' => $this->faker->randomElement(Parameter::where('grp', 'PENGELUARAN KAS')->get()),
        ];
    }
}
