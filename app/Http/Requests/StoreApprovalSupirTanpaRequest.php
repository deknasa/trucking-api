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

        if (request()->showgambar == "true") {
            $validasi_gambar = "required";
        }else{
            $validasi_gambar = "";
        }
        if (request()->showketerangan == "true") {
            $validasi_Keterangan = "required";
        }else{
            $validasi_Keterangan = "";
        }
        return [
            "namasupir"=> "required",
            "noktp"=> ["required",'exists:supir,noktp'],
            "keterangan_statusapproval"=> $validasi_Keterangan,
            "gambar_statusapproval"=> $validasi_gambar,
            "tglbatas" => ['required','date_format:d-m-Y',new DateApprovalTradoGambar()],
        ];
    }
}