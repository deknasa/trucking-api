<?php

namespace Database\Seeders;

use App\Models\Trado;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TradoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete Trado");
        DB::statement("DBCC CHECKIDENT ('Trado', RESEED, 1);");

        trado::create(['keterangan' => 'BK 1234 AB', 'statusaktif' => '1', 'statusgerobak' => '247', 'kmawal' => '22000', 'kmakhirgantioli' => '22000', 'tglakhirgantioli' => '2021/12/1', 'tglstnkmati' => '2022/12/12', 'tglasuransimati' => '2022/12/10', 'tahun' => '2010', 'akhirproduksi' => '-', 'merek' => 'HYNO', 'norangka' => 'AB123456', 'nomesin' => 'AB123456', 'nama' => 'PT. TRANSPORINDO AGUNG SEJAHTERA', 'nostnk' => 'AB123456', 'alamatstnk' => 'JL. KARTINI DALAM NO 15', 'modifiedby' => 'ADMIN', 'tglstandarisasi' => '1900/1/1', 'tglserviceopname' => '1900/1/1', 'statusstandarisasi' => '17', 'keteranganprogressstandarisasi' => '-', 'statusjenisplat' => '21', 'tglspeksimati' => '2022/10/1', 'tglpajakstnk' => '2022/12/10', 'tglgantiakiterakhir' => '2019/12/1', 'statusmutasi' => '23', 'statusvalidasikendaraan' => '24', 'tipe' => 'TRUCK', 'jenis' => 'TRUCK', 'isisilinder' => '8', 'warna' => 'HITAM', 'jenisbahanbakar' => 'SOLAR', 'jumlahsumbu' => '4', 'jumlahroda' => '10', 'model' => 'TRADO', 'nobpkb' => 'AB12345', 'statusmobilstoring' => '26', 'mandor_id' => '1', 'jumlahbanserap' => '1', 'statusappeditban' => '29', 'statuslewatvalidasi' => '30', 'photostnk' => '', 'photobpkb' => '', 'phototrado' => '',]);
        trado::create(['keterangan' => 'BK 4567 AB', 'statusaktif' => '1', 'statusgerobak' => '247', 'kmawal' => '22000', 'kmakhirgantioli' => '22000', 'tglakhirgantioli' => '2021/12/1', 'tglstnkmati' => '2022/12/12', 'tglasuransimati' => '2022/12/10', 'tahun' => '2010', 'akhirproduksi' => '-', 'merek' => 'HYNO', 'norangka' => 'ABCDEFG', 'nomesin' => 'ABCDEFG', 'nama' => 'PT. TRANSPORINDO AGUNG SEJAHTERA', 'nostnk' => 'AB123456', 'alamatstnk' => 'JL. KARTINI DALAM NO 15', 'modifiedby' => 'ADMIN', 'tglstandarisasi' => '1900/1/1', 'tglserviceopname' => '1900/1/1', 'statusstandarisasi' => '17', 'keteranganprogressstandarisasi' => '-', 'statusjenisplat' => '21', 'tglspeksimati' => '2022/10/1', 'tglpajakstnk' => '2022/12/10', 'tglgantiakiterakhir' => '2019/12/1', 'statusmutasi' => '23', 'statusvalidasikendaraan' => '24', 'tipe' => 'TRUCK', 'jenis' => 'TRUCK', 'isisilinder' => '8', 'warna' => 'HITAM', 'jenisbahanbakar' => 'SOLAR', 'jumlahsumbu' => '4', 'jumlahroda' => '10', 'model' => 'TRADO', 'nobpkb' => 'AB12345', 'statusmobilstoring' => '26', 'mandor_id' => '1', 'jumlahbanserap' => '1', 'statusappeditban' => '29', 'statuslewatvalidasi' => '30', 'photostnk' => '', 'photobpkb' => '', 'phototrado' => '',]);
    }
}
