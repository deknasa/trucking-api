<?php

namespace App\Http\Requests;

use App\Rules\ValidasiApprovalPelunasanHutang;
use Illuminate\Foundation\Http\FormRequest;

class ApprovalPelunasanHutangRequest extends FormRequest
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
            'bayarId'  => ['required',new ValidasiApprovalPelunasanHutang()]
        ];
    }

    public function messages()
    {
        return [
            'bayarId.required' => 'PELUNASAN WAJIB DIPILIH'
        ];
    }
}
