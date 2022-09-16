<?php

namespace Database\Factories;

use App\Models\Parameter;
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
        $statusNonApproval = Parameter::where('grp', '=', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
        
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
            'top' => 1.0,
            'statusapproval' => $statusNonApproval->id,
            'userapproval' => $this->faker->word(),
            'tglapproval' => $this->faker->date(),
            'statustas' => 1,
            'jenisemkl' => $this->faker->word(),
            'modifiedby' => 'ADMIN',
        ];
    }
}
