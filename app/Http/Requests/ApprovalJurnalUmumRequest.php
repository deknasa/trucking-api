<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Foundation\Http\FormRequest;

class ApprovalJurnalUmumRequest extends FormRequest
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
            'jurnalId' => 'required'
        ];
    }
    public function messages()
    {
        return [
            'jurnalId.required' => 'no bukti transaksi '.app(ErrorController::class)->geterror('WP')->keterangan,
        ];
    }
}
