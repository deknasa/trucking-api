<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ParameterController;
use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use Illuminate\Validation\Rule;

class LaporanRekapTitipanEmklRequest extends FormRequest
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
            'periode' => [
                'required', 'date_format:m-Y',
            ],
        ];
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
            'periode.after_or_equal' => ':attribute ' . $controller->geterror('NTLK')->keterangan.' '. $this->tgldari,
        ];
    }    

}
