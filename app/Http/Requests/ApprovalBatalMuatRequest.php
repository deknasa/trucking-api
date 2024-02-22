<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Rules\ApprovalBatalMuat;
use Illuminate\Foundation\Http\FormRequest;

class ApprovalBatalMuatRequest extends FormRequest
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
            'Id' => ['required', new ApprovalBatalMuat()]
        ];
    }
    public function messages()
    {
        return [
            'Id.required' => request()->table.' ' . app(ErrorController::class)->geterror('WP')->keterangan,
        ];
    }
}
