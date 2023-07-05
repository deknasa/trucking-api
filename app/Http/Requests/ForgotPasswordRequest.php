<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
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
            'user' => 'required|exists:user,user'
        ];
    }

    public function messages()
    {
       
        return [
            'user.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
            'user.exists' => ':attribute' . ' ' . 'TIDAK ADA',
        ];
    }
}
