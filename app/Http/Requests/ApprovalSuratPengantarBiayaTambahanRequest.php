<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Rules\ValidasiApprovalSuratPengantarBiayaTambahan;
use Illuminate\Foundation\Http\FormRequest;

class ApprovalSuratPengantarBiayaTambahanRequest extends FormRequest
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
            'id' => 'required',
            'id.*' => ['required', new ValidasiApprovalSuratPengantarBiayaTambahan()]
        ];
    }

    public function messages()
    {
        return [
            'id.required' => 'biaya tambahan '.app(ErrorController::class)->geterror('WP')->keterangan,
        ];
    }
}
