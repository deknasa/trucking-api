<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Foundation\Http\FormRequest;

class ApprovalPenerimaanHeaderRequest extends FormRequest
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
            'penerimaanId' => 'required'
        ];
    }
    public function messages()
    {
        return [
            'penerimaanId.required' => 'PENERIMAAN '.app(ErrorController::class)->geterror('WP')->keterangan,
        ];
    }
}