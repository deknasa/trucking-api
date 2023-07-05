<?php

namespace App\Http\Requests;

use App\Rules\ValidasiDetail;
use Illuminate\Foundation\Http\FormRequest;

class TransferAkunPusatRequest extends FormRequest
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
        $jumlahdetail = $this->jumlahdetail ?? 0;
        return [
            'cabang' => ['required',new ValidasiDetail($jumlahdetail)],
        ];
    }
}
