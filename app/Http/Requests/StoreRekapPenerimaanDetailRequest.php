<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\ErrorController;


class StoreRekapPenerimaanDetailRequest extends FormRequest
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
            // "rekappenerimaan_id" => 'required',
            "tgltransaksi_detail" => 'required',
            "penerimaan_nobukti" => 'required',
            "nominal" => 'required',
            // "keterangan_detail" => 'required',
            // "modifiedby" => 'required',
            
        ];
    }
    public function attributes()
    {
        return [
            "rekappenerimaan_id" =>"rekap penerimaan",
            "keterangan_detail" =>"keterangan detail",
            "tgltransaksi_detail" =>"tgl transaksi detail",
            "penerimaan_nobukti" =>"penerimaan nobukti",
            "nominal" =>"nominal"
        ];
    }
    public function messages()
    {
        return [
            "rekappenerimaan_id.required" => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
            "tgltransaksi_detail.required" => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
            "penerimaan_nobukti.required" => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
            "nominal.required" => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
           //  "keterangan_detail.required" => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
        ];
    }

    
}
