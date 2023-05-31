<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Foundation\Http\FormRequest;

class ApprovalNotaKreditRequest extends FormRequest
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
            'kreditId' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'kreditId.required' => 'nota kredit '.app(ErrorController::class)->geterror('WP')->keterangan,
        ];
    }
}
