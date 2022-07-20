<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TradoRequest extends FormRequest
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
            'tglstnkmati' => 'required',
            'tglasuransimati' => 'required',
            'tahun' => 'required',
            'akhirproduksi' => 'required',
            'tglstandarisasi' => 'required',
            'tglserviceopname' => 'required',
            'statusstandarisasi' => 'required',
            'tglspeksimati' => 'required',
            'statusmutasi' => 'required',
            'statusvalidasikendaraan' => 'required',
            'mandor_id' => 'required',
            // 'g_trado' => 'required',
            // 'g_bpkb' => 'required',
            // 'g_stnk' => 'required'
        ];
    }

    public function attributes()
    {
        return [
            'statusaktif' => 'Status Aktif',
            'tglstnkmati' => 'Tgl STNK Mati',
            'tglasuransimati' => 'Tgl Asuransi Mati',
            'tahun' => 'Tahun',
            'akhirproduksi' => 'Akhir Produksi',
            'tglstandarisasi' => 'Tgl Standarisasi',
            'tglserviceopname' => 'Tgl Service Opname',
            'statusstandarisasi' => 'Status Standarisasi',
            'tglspeksimati' => 'Tgl Speksi Mati',
            'statusmutasi' => 'Status Mutasi',
            'statusvalidasikendaraan' => 'Status Validasi Kendaraan',
            'mandor_id' => 'Mandor',
            /*'g_trado' => 'Gambar Trado',
            'g_bpkb' => 'Gambar BPKB',
            'g_stnk' => 'Gambar STNK'*/
        ];
    }
}
