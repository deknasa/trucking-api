<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ParameterController;
use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use Illuminate\Validation\Rule;

class RangeExportReportRequest extends FormRequest
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
            'limit' => ['gt:0']
        ];
    }

    public function attributes()
    {
        return [
            'limit' => 'sampai',
        ];
    }

    public function messages()
    {
        return [
            'limit.gt' => (new ErrorController)->geterror('HDSD')->keterangan.' nilai dari'
        ];
    }
}
