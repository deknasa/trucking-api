<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;
use App\Rules\ValidasiTradoTambahanAbsensiApproval;

class ApprovalTradoTambahanAbsensiRequest extends FormRequest
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
            'tradoTambahanId' => ['required'],
            'tradoTambahanId.*' => [new ValidasiTradoTambahanAbsensiApproval()]

        ];
    }
    public function messages()
    {
        return [
            'tradoTambahanId.required' => 'Trado Tambahan '.app(ErrorController::class)->geterror('WP')->keterangan,
        ];
    }
}
