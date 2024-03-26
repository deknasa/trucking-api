<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Rules\ValidasiSupirSerapApproval;
use App\Rules\ValidasiSupirSerapApprovalAbsensi;
use Illuminate\Foundation\Http\FormRequest;

class ApprovalSupirSerapRequest extends FormRequest
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
            'serapId' => ['required'],
            'serapId.*' => [new ValidasiSupirSerapApproval(),new ValidasiSupirSerapApprovalAbsensi()]
        ];
    }
    
    public function messages()
    {
        return [
            'serapId.required' => 'SUPIR SERAP '.app(ErrorController::class)->geterror('WP')->keterangan,
        ];
    }
}
