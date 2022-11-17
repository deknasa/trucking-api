<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTradoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'keterangan' => 'required',
            'statusaktif' => 'required',
            'kmawal' => 'required',
            'kmakhirgantioli' => 'required',
            'tglakhirgantioli' => 'required',
            'tglstnkmati' => 'required',
            'tglasuransimati' => 'required',
            'tahun' => 'required',
            'akhirproduksi' => 'required',
            'merek' => 'required',
            'norangka' => 'required',
            'nomesin' => 'required',
            'nama' => 'required',
            'nostnk' => 'required',
            'alamatstnk' => 'required',
            'tglstandarisasi' => 'required',
            'tglserviceopname' => 'required',
            'statusstandarisasi' => 'required',
            'keteranganprogressstandarisasi' => 'required',
            'statusjenisplat' => 'required',
            'tglspeksimati' => 'required',
            'tglpajakstnk' => 'required',
            'tglgantiakiterakhir' => 'required',
            'statusmutasi' => 'required',
            'statusvalidasikendaraan' => 'required',
            'tipe' => 'required',
            'jenis' => 'required',
            'isisilinder' => 'required',
            'warna' => 'required',
            'jenisbahanbakar' => 'required',
            'jumlahsumbu' => 'required',
            'jumlahroda' => 'required',
            'model' => 'required',
            'nobpkb' => 'required',
            'statusmobilstoring' => 'required',
            'mandor_id' => 'required|int|exists:mandor,id',
            'jumlahbanserap' => 'required',
            'statusappeditban' => 'required',
            'statuslewatvalidasi' => 'required',
            'phototrado' => 'required|array',
            'phototrado.*' => 'required|image',
            'photobpkb' => 'required|array',
            'photobpkb.*' => 'required|image',
            'photostnk' => 'required|array',
            'photostnk.*' => 'required|image',
        ];
    }

    public function attributes()
    {
        return [
            'keterangan' => 'Keterangan',
            'statusaktif' => 'Status Aktif',
            'kmawal' => 'KM Awal',
            'kmakhirgantioli' => 'KM Akhir Ganti Oli',
            'tglakhirgantioli' => 'Tgl Akhir Ganti Oli',
            'tglstnkmati' => 'Tgl STNK Mati',
            'tglasuransimati' => 'Tgl Asuransi Mati',
            'tahun' => 'Tahun',
            'akhirproduksi' => 'Tahun Akhir Produksi',
            'merek' => 'Merek',
            'norangka' => 'No Rangka',
            'nomesin' => 'No Mesin',
            'nama' => 'Nama',
            'nostnk' => 'No STNK',
            'alamatstnk' => 'Alamat STNK',
            'tglstandarisasi' => 'Tgl Standarisasi',
            'tglserviceopname' => 'Tgl Service Opname',
            'statusstandarisasi' => 'Status Standarisasi',
            'keteranganprogressstandarisasi' => 'Ket. Progres Standarisasi',
            'statusjenisplat' => 'Jenis Plat',
            'tglspeksimati' => 'Tgl Speksi Mati',
            'tglpajakstnk' => 'Tgl Pajak STNK',
            'tglgantiakiterakhir' => 'Tgl Ganti Aki Terakhir',
            'statusmutasi' => 'Status Mutasi',
            'statusvalidasikendaraan' => 'Status Validasi Kendaraan',
            'tipe' => 'Tipe',
            'jenis' => 'Jenis',
            'isisilinder' => 'Isi Silinder',
            'warna' => 'Warna',
            'jenisbahanbakar' => 'Jenis Bahan Bakar',
            'jumlahsumbu' => 'Jumlah Sumbu',
            'jumlahroda' => 'Jumlah Roda',
            'model' => 'Model',
            'nobpkb' => 'No BPKB',
            'statusmobilstoring' => 'Status Mobil Storing',
            'mandor_id' => 'Mandor',
            'jumlahbanserap' => 'Jumlah Ban Serap',
            'statusappeditban' => 'Status App. Edit Ban',
            'statuslewatvalidasi' => 'Status Lewat Validasi'
        ];
    }
}
