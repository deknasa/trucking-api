<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSuratPengantarRequest extends FormRequest
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
            'jobtrucking' => 'required',
            'tglbukti' => 'required',
            'keterangan' => 'required',
            'dari' => 'required',
            'sampai' => 'required',
            'statusperalihan' => 'required',
            'statuscontainer' => 'required',
            'trado' => 'required',
            'supir' => 'required',
            'statuslongtrip' => 'required',
            'nosp' => 'required',
            'statusritasiomset' => 'required',
            'cabang' => 'required',
            // 'qtyton' => 'required|numeric|gt:0',
            // 'totalton' => 'required|numeric|gt:0',
        ];
    }

    public function attributes()
    {
        return [
            'jobtrucking' => 'job trucking',
            'tglbukti' => 'tgl transaksi',
            'statusperalihan' => 'status peralihan',
            'statuscontainer' => 'status container',
            'statuslongtrip' => 'status longtrip',
            'statusritasiomset' => 'status ritasi omset'

        ];
    }

    // public function messages()
    // {
    //     return [
    //         'qtyton.gt' => 'QTY Ton tidak boleh kosong',
    //         'upahbongkardepo.gt' => 'Upah Bongkar Depo tidak boleh kosong',
    //         'upahmuatdepo.gt' => 'Upah Muat Depo tidak boleh kosong',
    //     ];
    // }
}
