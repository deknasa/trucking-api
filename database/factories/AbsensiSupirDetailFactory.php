<?php

namespace Database\Factories;

use App\Models\AbsensiSupirHeader;
use App\Models\Supir;
use App\Models\Trado;
use Illuminate\Database\Eloquent\Factories\Factory;

class AbsensiSupirDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $absensi = AbsensiSupirHeader::all();
        $trado = Trado::all();
        $supir = Supir::all();
        
        return [
            'absensi_id' => $this->faker->randomElement($absensi),
            'nobukti' => $this->faker->words(2, true),
            'trado_id' => $this->faker->randomElement($supir),
            'supir_id' => $this->faker->randomElement($supir),
            'keterangan' => $this->faker->words(2, true),
            'uangjalan' => rand(10000, 100000),
            'absen_id' => 1,
            'jam' => date('H:i:s', rand(1,54000)),
        ];
    }
}
