<?php

namespace App\Http\Requests;

use App\Rules\DateApprovalTradoGambar;
use Illuminate\Foundation\Http\FormRequest;

class StoreApprovalSupirTanpaRequest extends FormRequest
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
            "noktp"=> ["required",'exists:supir,noktp'],
            "keterangan_statusapproval"=> "required_without_all:gambar_statusapproval",
            "gambar_statusapproval"=> "required_without_all:keterangan_statusapproval",
            "tglbatas" => ['required','date_format:d-m-Y',new DateApprovalTradoGambar()],
        ];
    }
}