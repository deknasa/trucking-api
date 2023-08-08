<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\ErrorController;


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
        $spb = DB::table('parameter')->where('grp', 'SPB STOK')->where('subgrp', 'SPB STOK')->first();
        $po = DB::table('parameter')->where('grp', 'PO STOK')->where('subgrp', 'PO STOK')->first();
        $do = DB::table('parameter')->where('grp', 'DO STOK')->where('subgrp', 'DO STOK')->first();
        $kor = DB::table('parameter')->where('grp', 'KOR STOK')->where('subgrp', 'KOR STOK')->first();
        $pg = DB::table('parameter')->where('grp', 'PG STOK')->where('subgrp', 'PG STOK')->first();
        $korv = DB::table('penerimaanstok')->where('kodepenerimaan', 'KORV')->first();
        $reuse = DB::table('parameter')->where('grp', 'REUSE STOK')->where('subgrp', 'REUSE STOK')->first();
        $requiredQty = Rule::requiredIf((request()->penerimaanstok_id == $spb->text));
        
        
       
        return [
            'detail_stok' => 'required|array|distinct',
            'detail_stok.*' => ['required','distinct'],
            'detail_stok_id' => 'required|array|distinct',
            'detail_stok_id.*' => ['required','distinct'],
            
            // 'detail_qty.*' => function ($attribute, $value, $fail) use ($spb, $po, $do, $kor, $pg, $reuse){ 
            //     if((request()->penerimaanstok_id == $spb->text)||(request()->penerimaanstok_id == $po->text)||(request()->penerimaanstok_id == $do->text)||(request()->penerimaanstok_id == $reuse->text) && ($value <= 0)){
            //         $fail(app(ErrorController::class)->geterror('GT-ANGKA-0')->keterangan);
            //     }
            // },
            
            'detail_harga.*' => function ($attribute, $value, $fail) use ($spb, $po, $do, $kor, $pg, $reuse){
                if((request()->penerimaanstok_id == $spb->text) && ($value <= 0)){
                    $fail(app(ErrorController::class)->geterror('GT-ANGKA-0')->keterangan);
                }
            },
            'detail_vulkanisirke.*'=>[
                'numeric',
                'nullable',
                function ($attribute, $value, $fail) use ($korv){
                    if(($korv->id == request()->penerimaanstok_id) && ($value <= 0)){
                        
                        $fail(app(ErrorController::class)->geterror('GT-ANGKA-0')->keterangan);
                    }
                },
            ],
            'detail_qty.*' => [
                'numeric',
                function ($attribute, $value, $fail) use ($korv){
                    // dd(($value <= 0),$value);
                    if(($korv->id != request()->penerimaanstok_id) && ($value <= 0)){
                        $fail(app(ErrorController::class)->geterror('GT-ANGKA-0')->keterangan);
                    }
                },
                // 'gt:0'
            ],
            'detail_persentasediscount.*' => 'numeric|max:100',
                
            'penerimaanstokheader_id.*' => 'required',
            'detail_keterangan.*' => 'required',
        ];
    }

    public function attributes()
    {
        return [
            'detail_stok_id.*' => 'stok',
            'detail_stok.*' => 'stok',
            'detail_keterangan.*' => 'detail keterangan',
            'detail_qty.*' => 'qty',
            'detail_vulkanisirke.*' => 'vulkanisir ke',
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
            'penerimaanstokheader_id.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
            'detail_keterangan.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
            'detail_persentasediscount.max' => ':attribute' . ' ' . app(ErrorController::class)->geterror('MIN')->keterangan,
        ];
    }
        
}
