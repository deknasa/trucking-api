<?php

namespace Database\Factories;

use App\Models\Parameter;
use App\Models\Supir;
use App\Models\Zona;
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
        $zonas = Zona::all();
        
        return [
            'namasupir' => $this->faker->words(2, true),
            'alamat' => $this->faker->words(2, true),
            'kota' => $this->faker->words(2, true),
            'telp' => $this->faker->words(2, true),
            'statusaktif' => $this->faker->randomElement(Parameter::where('grp', 'STATUS AKTIF')->get()),
            'nominaldepositsa' => 1,
            'depositke' => 1,
            'tglmasuk' => $this->faker->date(),
            'nominalpinjamansaldoawal' => 1,
            'supirold_id' => $this->faker->randomElement(Supir::all()),
            'tglexpsim' => $this->faker->date(),
            'nosim' => $this->faker->words(2, true),
            'keterangan' => $this->faker->words(2, true),
            'noktp' => $this->faker->words(2, true),
            'nokk' => $this->faker->words(2, true),
            'statusadaupdategambar' => $this->faker->randomElement(Parameter::where('grp', 'STATUS ADA UPDATE GAMBAR')->get()),
            'statusluarkota' => $this->faker->randomElement(Parameter::where('grp', 'STATUS LUAR KOTA')->get()),
            'statuszonatertentu' => $this->faker->randomElement(Parameter::where('grp', 'ZONA TERTENTU')->get()),
            'zona_id' => $this->faker->randomElement($zonas),
            'angsuranpinjaman' => 1,
            'plafondeposito' => 1,
            'photosupir' => $this->faker->words(2, true),
            'photoktp' => $this->faker->words(2, true),
            'photosim' => $this->faker->words(2, true),
            'photokk' => $this->faker->words(2, true),
            'photoskck' => $this->faker->words(2, true),
            'photodomisili' => $this->faker->words(2, true),
            'keteranganresign' => $this->faker->words(2, true),
            'statusblacklist' => $this->faker->randomElement(Parameter::where('grp', 'BLACKLIST SUPIR')->get()),
            'tglberhentisupir' => $this->faker->date(),
            'tgllahir' => $this->faker->date(),
            'tglterbitsim' => $this->faker->date(),
            'modifiedby' => $this->faker->words(2, true),
        ];
    }
}
