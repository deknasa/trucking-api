<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;

class StoreGajiSupirHeaderRequest extends FormRequest
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
            //
            'supir' => 'required',
            'tgldari' => 'required',
            'tglsampai' => 'required',
            'tglbukti' => [
                'required','date_format:d-m-Y',
                new DateTutupBuku()
            ],
        ];
    }

    public function attributes() {
        return [
            'tgldari' => 'Tanggal Dari',
            'tglsampai' => 'Tanggal Sampai',
            'tglbukti' => 'Tanggal Bukti'
        ];
    }
    public function messages()
    {
        return [
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
        ];
    }
    
}
