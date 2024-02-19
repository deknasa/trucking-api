<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Rules\ValidasiApprovalHutang;
use Illuminate\Foundation\Http\FormRequest;

class ApprovalHutangHeaderRequest extends FormRequest
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
            'hutangId' => ['required', new ValidasiApprovalHutang()],
        ];
    }
    public function messages()
    {
        return [
            'hutangId.required' => 'HUTANG '.app(ErrorController::class)->geterror('WP')->keterangan,
        ];
    }
}
