<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Foundation\Http\FormRequest;

class ApprovalRekapPenerimaanRequest extends FormRequest
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
            'rekapId' => 'required'
        ];
    }
    public function messages()
    {
        return [
            'rekapId.required' => 'no bukti transaksi '.app(ErrorController::class)->geterror('WP')->keterangan,
        ];
    }
}
