<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use App\Rules\DestroyAgen;
use App\Rules\DestroyBank;
use App\Rules\DestroySupplier;
use Illuminate\Validation\Rule;

class DestroySupplierRequest extends FormRequest
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
        if (request()->from == 'tas') {
            return [];
        }
      
        return [
            'id' => new DestroySupplier(),
        ];
      
    }

    // public function messages()
   
    // }
}
