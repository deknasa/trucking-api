<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\ErrorController;

class StorePengeluaranStokDetailRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        $spk = DB::table('parameter')->where('grp', 'SPK STOK')->where('subgrp', 'SPK STOK')->first();
        $retur = DB::table('parameter')->where('grp', 'RETUR STOK')->where('subgrp', 'RETUR STOK')->first();
        $kor = DB::table('parameter')->where('grp', 'KOR MINUS STOK')->where('subgrp', 'KOR MINUS STOK')->first();
        $korv = DB::table('pengeluaranstok')->where('kodepengeluaran', 'KORV')->first();

        return [
            'detail_stok' => 'required|array|distinct',
            'detail_stok.*' => ['required','distinct'],
            'detail_stok_id' => 'required|array|distinct',
            'detail_stok_id.*' => ['required','distinct'],
            'detail_harga.*' => function ($attribute, $value, $fail) use ($spk, $retur, $kor){
                if((request()->pengeluaranstok_id == $retur->text) && ($value <= 0)){
                    $fail(app(ErrorController::class)->geterror('GT-ANGKA-0')->keterangan);
                }
            },

            'detail_qty.*' => [
                'numeric',
                function ($attribute, $value, $fail) use ($korv){
                    if(($korv->id != request()->pengeluaranstok_id) && ($value <= 0)){
                        $fail(app(ErrorController::class)->geterror('GT-ANGKA-0')->keterangan);
                    }
                },
            ],  
            'detail_vulkanisirke.*' => [
                'numeric',
                'nullable',
                function ($attribute, $value, $fail) use ($korv){
                    if(($korv->id == request()->pengeluaranstok_id) && ($value <= 0)){
                        $fail(app(ErrorController::class)->geterror('GT-ANGKA-0')->keterangan);
                    }
                },
            ],  
            'detail_persentasediscount.*' => 'numeric|max:100',
                
            'pengeluaranstokheader_id.*' => 'required',
            'detail_keterangan.*' => 'required',
        ];
    }

    public function attributes()
    {
        return [
            'detail_stok_id.*' => 'stok',
            'detail_stok.*' => 'stok',
            'detail_keterangan.*' => 'detail keterangan',
            'detail_vulkanisirke.*' => 'vulkanisir ke',
            'detail_qty.*' => 'qty',
            'detail_harga.*' => 'harga',
            'detail_persentasediscount.*' => 'persentase discount',
        ];
    }

    public function messages()
    {
        return [
            'detail_stok.distinct' => ':attribute' . ' ' . app(ErrorController::class)->geterror('spi')->keterangan,
            'detail_stok.*.distinct' => ':attribute' . ' ' . app(ErrorController::class)->geterror('spi')->keterangan,
            'detail_stok_id.distinct' => ':attribute' . ' ' . app(ErrorController::class)->geterror('spi')->keterangan,
            'detail_stok_id.*.distinct' => ':attribute' . ' ' . app(ErrorController::class)->geterror('spi')->keterangan,

            'detail_qty.gt' => ':attribute' . ' ' . app(ErrorController::class)->geterror('GT-ANGKA-0')->keterangan,
            'detail_harga.gt' => ':attribute' . ' ' . app(ErrorController::class)->geterror('GT-ANGKA-0')->keterangan,
            
            'detail_qty.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
            'detail_harga.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
            
            'detail_stok.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
            'detail_stok.unique' => ':attribute' . ' ' . app(ErrorController::class)->geterror('spi')->keterangan,
            'pengeluaranstokheader_id.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
            'detail_keterangan.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
            'detail_persentasediscount.max' => ':attribute' . ' ' . app(ErrorController::class)->geterror('MIN')->keterangan,
        ];
    }
}
