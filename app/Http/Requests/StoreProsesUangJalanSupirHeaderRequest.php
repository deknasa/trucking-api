<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;

class StoreProsesUangJalanSupirHeaderRequest extends FormRequest
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
            "tglbukti" => [
                "required",'date_format:d-m-Y',
                new DateTutupBuku()
            ],
            'absensisupir' => 'required',
            'supir' => 'required',
            'trado'=> 'required',
            'keterangantransfer' => 'required|array',
            'keterangantransfer.*' => 'required',
            'nilaitransfer' => 'required|array',
            'nilaitransfer.*' => 'required|gt:0|numeric',
            'banktransfer' => 'required|array',
            'banktransfer.*' => 'required',
            'nilaiadjust' => 'required|gt:0|numeric',
            'keteranganadjust' => 'required',
            'bankadjust' => 'required',
        ];
    }
    public function attributes()
    {
        $attributes = [
            'keterangantransfer.*' => 'keterangan transfer',
            'nilaitransfer.*' => 'nilai transfer',
            'banktransfer.*' => 'bank transfer',
            'keteranganadjust' => 'keterangan adjust transfer',
            'nilaiadjust' => 'nilai adjust transfer',
            'bankadjust' => 'bank adjust transfer',
        ];
        
        return $attributes;
    }

    public function messages() 
    {
        return [
            'nilaitransfer.*.gt' => 'nilai transfer Tidak Boleh Kosong dan Harus Lebih Besar Dari 0',
            'nilaiadjust.gt' => 'nilai adjust transfer Tidak Boleh Kosong dan Harus Lebih Besar Dari 0',
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
        ];
    }
}
