<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Rules\ApprovalEditTujuan;
use Illuminate\Foundation\Http\FormRequest;

class ApprovalEditTujuanRequest extends FormRequest
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
            'Id' => ['required', new ApprovalEditTujuan()]
        ];
    }
    public function messages()
    {
        return [
            'Id.required' => request()->table.' ' . app(ErrorController::class)->geterror('WP')->keterangan,
        ];
    }
}
