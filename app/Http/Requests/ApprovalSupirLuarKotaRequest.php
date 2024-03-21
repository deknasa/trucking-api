<?php

namespace App\Http\Requests;

use App\Rules\validasiApprovalTglBatasLuarKota;
use App\Rules\validasiKeteranganLuarKota;
use Illuminate\Foundation\Http\FormRequest;

class ApprovalSupirLuarKotaRequest extends FormRequest
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
            'statusluarkota' => 'required',
            'tglbatas' => new validasiApprovalTglBatasLuarKota(),
            'keterangan' => new validasiKeteranganLuarKota()
        ];
    }
    public function attributes()
    {
        return [
            'statusluarkota' => 'status luar kota'
        ];
    }
}
