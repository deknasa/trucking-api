<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use App\Rules\DestroyBank;
use App\Rules\DestroyBankPelanggan;
use App\Rules\DestroyJurnalUmum;
use App\Rules\DestroyPelanggan;
use App\Rules\DestroyPenerima;
use Illuminate\Validation\Rule;

class DestroyPelangganRequest extends FormRequest
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
            'id' => new DestroyPelanggan(),
        ];
      
    }

    // public function messages()
   
    // }
}
