<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ParameterController;
use App\Http\Controllers\Api\ErrorController;
use Illuminate\Validation\Rule;

class GetIndexRangeRequest extends FormRequest
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

        //  dd(date('Y-m-d', strtotime($this->tgldari)));
        $rules =  [
            'tgldari' => ['required', 'date_format:d-m-Y' ],
            'tglsampai' => ['required', 'date_format:d-m-Y','after_or_equal:'.date('Y-m-d', strtotime($this->tgldari))  ],
        ];

        return $rules;
    }

    public function attributes()
    {
        return [
            'tgldari' => 'tanggal dari',
            'tglsampai' => 'tanggal sampai',
        ];
    }

    public function messages()
    {
        $controller = new ErrorController;

        return [
            'tglsampai.after_or_equal' => ':attribute ' . $controller->geterror('NTLK')->keterangan.' '. $this->tgldari,
        ];
    }    

}
