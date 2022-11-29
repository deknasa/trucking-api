<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRitasiRequest extends FormRequest
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
            'tglbukti' => 'required',
            'statusritasi' => 'required',
            'suratpengantar_nobukti' => 'required',
            'dari' => 'required',
            'sampai' => 'required',
            'trado' => 'required',
            'supir' => 'required',
        ];
    }

    public function attributes()
    {
        return [
            'tglbukti' => 'tanggal bukti',
            'statusritasi' => 'status ritasi',
            'suratpengantar_nobukti' => 'No bukti surat pengantar',
        ];
    }
}
