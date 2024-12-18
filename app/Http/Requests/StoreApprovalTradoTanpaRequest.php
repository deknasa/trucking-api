<?php

namespace App\Http\Requests;

use App\Rules\DateUsedTradoTrip;
use App\Rules\DateApprovalTradoGambar;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ValidasiTradoTanpaGambarGambar;
use App\Rules\ValidasiTradoTanpaGambarKeterangan;

class StoreApprovalTradoTanpaRequest extends FormRequest
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
            "kodetrado"=> "required",
            // "keterangan_statusapproval"=> "required_without_all:gambar_statusapproval",
            // "gambar_statusapproval"=> "required_without_all:keterangan_statusapproval",
            "keterangan_statusapproval" => [new ValidasiTradoTanpaGambarKeterangan()],
            "gambar_statusapproval" => [new ValidasiTradoTanpaGambarGambar  ()],
            "tglbatas" => ['date_format:d-m-Y','nullable',new DateUsedTradoTrip()]
            // "tglbatas" => ['required','date_format:d-m-Y',new DateApprovalTradoGambar()],
        ];
    }
}
