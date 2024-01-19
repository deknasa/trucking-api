<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Foundation\Http\FormRequest;

class ApprovalPengajuanTripInapRequest extends FormRequest
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
            'Id' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'Id.required' => 'pengajuan trip inap ' . app(ErrorController::class)->geterror('WP')->keterangan,
        ];
    }
}
