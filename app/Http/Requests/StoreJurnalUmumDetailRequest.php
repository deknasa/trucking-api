<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;

class StoreJurnalUmumDetailRequest extends FormRequest
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
            'ketcoadebet_detail' => 'required|array',
            'ketcoadebet_detail.*' => 'required',
            'ketcoakredit_detail' => 'required|array',
            'ketcoakredit_detail.*' => 'required',
            'nominal_detail' => 'required|array',
            'nominal_detail.*' => 'required|numeric|gt:0',
            'keterangan_detail' => 'required|array',
            'keterangan_detail.*' => 'required'
        ];
    }

    public function attributes()
    {
        return [
            'ketcoadebet_detail.*' => 'nama perkiraan (Debet)',
            'ketcoakredit_detail.*' => 'nama perkiraan (Kredit)',
            'nominal_detail.*' => 'Nominal',
            'keterangan_detail.*' => 'Keterangan',
        ];
    }

    public function messages() 
    {
        return [
            'nominal_detail.*.gt' => 'Nominal Tidak Boleh Kosong dan Harus Lebih Besar Dari 0',
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan
        ];
    }

}
