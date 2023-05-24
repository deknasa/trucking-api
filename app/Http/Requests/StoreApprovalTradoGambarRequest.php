<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Rules\DateApprovalTradoGambar;
use Illuminate\Foundation\Http\FormRequest;

class StoreApprovalTradoGambarRequest extends FormRequest
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
            'kodetrado' => 'required',
            'tglbatas' => ['required','date_format:d-m-Y',new DateApprovalTradoGambar()],
            'statusapproval' => 'required'
        ];
    }

    public function attributes()
    {
        return [
            'kodetrado' => 'kode trado',
            'tglbatas' => 'tgl batas',
            'statusapproval' => 'status approval'
        ];
    }
    public function messages() 
    {
        return [
            'tglbatas.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
        ];
    }
}
