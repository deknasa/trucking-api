<?php

namespace Database\Factories;

use App\Models\Mandor;
use App\Models\Parameter;
use App\Models\Trado;
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
            'keterangan' => $this->faker->word(),
            'statusaktif' => $this->faker->randomElement(Parameter::where('grp', 'STATUS AKTIF')->get()),
            'kmawal' => $this->faker->randomFloat(),
            'kmakhirgantioli' => $this->faker->randomFloat(),
            'tglakhirgantioli' => $this->faker->date(),
            'tglstnkmati' => $this->faker->date(),
            'tglasuransimati' => $this->faker->date(),
            'tahun' => $this->faker->word(),
            'akhirproduksi' => $this->faker->word(),
            'merek' => $this->faker->word(),
            'norangka' => $this->faker->word(),
            'nomesin' => $this->faker->word(),
            'nama' => $this->faker->word(),
            'nostnk' => $this->faker->word(),
            'alamatstnk' => $this->faker->word(),
            'modifiedby' => $this->faker->word(),
            'tglstandarisasi' => $this->faker->date(),
            'tglserviceopname' => $this->faker->date(),
            'statusstandarisasi' => $this->faker->randomElement(Parameter::where('grp', 'STATUS STANDARISASI')->get()),
            'keteranganprogressstandarisasi' => $this->faker->word(),
            'statusjenisplat' => $this->faker->randomElement(Parameter::where('grp', 'JENIS PLAT')->get()),
            'tglspeksimati' => $this->faker->date(),
            'tglpajakstnk' => $this->faker->date(),
            'tglgantiakiterakhir' => $this->faker->date(),
            'statusmutasi' => $this->faker->randomElement(Parameter::where('grp', 'STATUS MUTASI')->get()),
            'statusvalidasikendaraan' => $this->faker->randomElement(Parameter::where('grp', 'STATUS VALIDASI KENDARAAN')->get()),
            'tipe' => $this->faker->word(),
            'jenis' => $this->faker->word(),
            'isisilinder' => $this->faker->numberBetween(),
            'warna' => $this->faker->word(),
            'jenisbahanbakar' => $this->faker->word(),
            'jumlahsumbu' => $this->faker->numberBetween(),
            'jumlahroda' => $this->faker->numberBetween(),
            'model' => $this->faker->word(),
            'nobpkb' => $this->faker->word(),
            'statusmobilstoring' => $this->faker->randomElement(Parameter::where('grp', 'STATUS MOBIL STORING')->get()),
            'mandor_id' => $this->faker->randomElement(Mandor::all()),
            'jumlahbanserap' => $this->faker->numberBetween(),
            'statusappeditban' => $this->faker->randomElement(Parameter::where('grp', 'STATUS APPROVAL EDIT BAN')->get()),
            'statuslewatvalidasi' => $this->faker->randomElement(Parameter::where('grp', 'STATUS LEWAT VALIDASI')->get()),
            'photostnk' => $this->faker->word(),
            'photobpkb' => $this->faker->word(),
            'phototrado' => $this->faker->word(),
        ];
    }
}
