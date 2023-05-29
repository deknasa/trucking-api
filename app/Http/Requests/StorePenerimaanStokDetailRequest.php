<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;


class StorePenerimaanStokDetailRequest extends FormRequest
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
    //         'detail_stok' => [
    //             'required',"array"
    //         ],
    //         'detail_stok_id.*' => [
    //             'required',
                
    //         ],
            
    //         'penerimaanstokheader_id.*' => 'required',
    //         'detail_keterangan.*' => 'required',
    //     ];
    // }

    // public function attributes()
    // {
    //     return [
    //         'detail_stok' => 'stok',
    //         'detail_keterangan' => 'detail keterangan',
    //     ];
    // }
    // public function messages()
    // {
    //     return [
    //         'detail_stok.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
    //         'detail_stok.unique' => ':attribute' . ' ' . app(ErrorController::class)->geterror('spi')->keterangan,
    //         'penerimaanstokheader_id.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
    //         'detail_keterangan.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
        ];
    }
        
}
