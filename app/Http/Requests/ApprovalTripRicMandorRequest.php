<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Rules\approvalMandorDouble;
use App\Rules\ApprovalTripRicMandor;
use Illuminate\Foundation\Http\FormRequest;

class ApprovalTripRicMandorRequest extends FormRequest
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
            'detail' => [
                // new ApprovalTripRicMandor(),
                new approvalMandorDouble()

            ]
        ];
    }
}
