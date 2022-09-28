<?php

namespace Database\Factories;

use App\Models\Cabang;
use App\Models\Parameter;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user' => $this->faker->firstName(),
            'name' => $this->faker->firstName(),
            'password' => bcrypt('123456'),
            'dashboard' => $this->faker->word(),
            'cabang_id' => $this->faker->randomElement(Cabang::all()),
            'karyawan_id' => $this->faker->randomElement(Parameter::where('grp', 'STATUS KARYAWAN')->get()),
            'statusaktif' => '1'
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }
}
