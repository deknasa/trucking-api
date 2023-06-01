<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ParameterController;
use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use App\Rules\ApprovalBukaCetak;
use Illuminate\Validation\Rule;

class GetIndexPencairanGiroRequest extends FormRequest
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
       
        $rules =  [
            'periode' => new ApprovalBukaCetak(),
            
            
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

      
        return 'format tanggal tidak valid';
    }    

}
