<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Foundation\Http\FormRequest;

class StoreApprovalSupirGambarRequest extends FormRequest
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
            "namasupir"=> "required",
            'noktp' => 'required|unique:approvalsupirgambar|min:16|max:16',
            "statusapproval"=> "required",
            "tglbatas"=> "required"
        ];
    }

    public function attributes()
    {
        return [
            'namasupir' => 'Nama Supir',
            'noktp' => 'No KTP',
            'statusapproval' => 'status approval',
            'tglbatas' => 'Tanggal absensi',
        ];
    }
    public function messages() 
    {
        $controller = new ErrorController;

        return [
            'noktp.max' => 'Max. 16 karakter',
            'noktp.min' => 'Min. 16 karakter',
            'noktp.unique' => ':attribute' . ' ' . $controller->geterror('SPI')->keterangan,
        ];
    }
}
