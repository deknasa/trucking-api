<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ValidasiApproval;
use App\Http\Controllers\Api\ErrorController;

class ApprovalValidasiApprovalRequest extends FormRequest
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
        // ValidasiApproval();
        $rules = [
            // 'tableId' => ['required','min:1',new ApprovalBukaCetak(),new BukaCetakSatuArah()],
            'bukti' => ['required',new ValidasiApproval()],
        ];
        // dd('test3a');
        return $rules;
    }

    public function attributes()
    {
        return [
            'bukti' => 'bukti',
        ];
    }

    public function messages()
    {
        $controller = new ErrorController;
        return [
            'bukti.required' => 'NO BUKTI ' . ' ' . $controller->geterror('WP')->keterangan,
        ];
    }
}
