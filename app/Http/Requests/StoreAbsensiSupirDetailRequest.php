<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;
use Illuminate\Validation\Rule;
use App\Rules\AbsensiSpuriUniqueSupirDetail;

use Illuminate\Support\Facades\DB;

class StoreAbsensiSupirDetailRequest extends FormRequest
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
            'trado' => 'required|array',
            'trado.*' => 'required',
            'supir' => 'nullable|array|distinct',
            'supir.*' => ['nullable','distinct'],
            'supir_ID' => 'nullable|array|distinct',
            'supir_ID.*' => ['nullable','distinct'],
            // 'uangjalan' => 'required|array',
            // 'uangjalan.*' => 'required|numeric|gt:0',
            // // 'absen' => 'required|array',
            // // 'absen.*' => 'required',
            // 'jam' => 'required|array',
            // 'jam.*' => 'required',
            // 'keterangan_detail' => 'required|array',
            // 'keterangan_detail.*' => 'required',
        ];
    }
    public function messages() 
    {
        return [
            'supir.distinct' => 'supir Tidak boleh sama',
            'supir.*.distinct' => 'supir Tidak boleh sama',
            'supir_id.distinct' => 'supir Tidak boleh sama',
            'supir_id.*.distinct' => 'supir Tidak boleh sama',


            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan
        ];
        
    }

    
}
